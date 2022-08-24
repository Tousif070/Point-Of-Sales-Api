<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\SaleVariation;
use App\Models\PurchaseVariation;
use App\Models\ProductModel;
use App\Models\Product;
use App\Models\Payment;
use App\Models\User;
use CAS;
use REC;
use DB;
use Exception;
use Carbon\Carbon;
use PDF;
use Mail;

class SaleTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("sale.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_transactions = SaleTransaction::join('users as u', 'u.id', '=', 'sale_transactions.finalized_by')
            ->join('users as u2', 'u2.id', '=', 'sale_transactions.customer_id')
            ->leftJoin('users as u3', 'u3.id', '=', 'sale_transactions.verified_by')
            ->join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->select(

                'sale_transactions.id',
                'sale_transactions.verification_status',
                'sale_transactions.verification_note',
                DB::raw('DATE_FORMAT(sale_transactions.transaction_date, "%m/%d/%Y") as date'),
                'sale_transactions.invoice_no',
                DB::raw('CONCAT_WS(" ", u2.first_name, u2.last_name) as customer'),
                // DB::raw('SUM(sv.quantity - sv.return_quantity) as total_items'), NOT NEEDED FOR NOW
                'sale_transactions.payment_status',
                DB::raw('sale_transactions.amount - IFNULL((select SUM(amount) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0) as total_payable'),
                DB::raw('IFNULL((select SUM(amount) from payments where transaction_id = sale_transactions.id and payment_for = "sale"), 0) as paid'),
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(sale_transactions.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by'),
                DB::raw('CONCAT_WS(" ", u3.first_name, DATE_FORMAT(sale_transactions.verified_at, "%m/%d/%Y %H:%i:%s")) as verified_by'),
                DB::raw('(select COUNT(invoice_no) from sale_return_transactions where sale_transaction_id = sale_transactions.id) as sale_return')

            )->groupBy('sale_transactions.id')
            ->orderBy('sale_transactions.transaction_date', 'desc')
            ->orderBy('sale_transactions.invoice_no', 'desc')
            ->get();

        return response(['sale_transactions' => $sale_transactions], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("sale.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'sale_transaction.transaction_date' => 'required | date',
            'sale_transaction.customer_id' => 'required | numeric',

            'sale_variations.*.product_id' => 'required | numeric',
            'sale_variations.*.purchase_variation_id' => 'required | numeric',
            'sale_variations.*.quantity' => 'required | numeric',
            'sale_variations.*.selling_price' => 'required | numeric'
        ], [
            'sale_transaction.transaction_date.required' => 'Please specify the transaction date !',
            'sale_transaction.transaction_date.date' => 'Please specify a valid date !',

            'sale_transaction.customer_id.required' => 'Please select the customer !',
            'sale_transaction.customer_id.numeric' => 'Customer ID should be numeric !',

            'sale_variations.*.product_id.required' => 'Product ID is required !',
            'sale_variations.*.product_id.numeric' => 'Product ID should be numeric !',

            'sale_variations.*.purchase_variation_id.required' => 'Purchase Variation ID is required !',
            'sale_variations.*.purchase_variation_id.numeric' => 'Purchase Variation ID should be numeric !',

            'sale_variations.*.quantity.required' => 'Quantity is required !',
            'sale_variations.*.quantity.numeric' => 'Quantity should be numeric !',

            'sale_variations.*.selling_price.required' => 'Selling Price is required !',
            'sale_variations.*.selling_price.numeric' => 'Selling Price should be numeric !'
        ]);

        DB::beginTransaction();

        try {

            $sale_transaction = new SaleTransaction();

            $sale_transaction->status = "Final";

            $sale_transaction->payment_status = "Due";

            $sale_transaction->transaction_date = Carbon::parse($request->sale_transaction['transaction_date']);

            $sale_transaction->customer_id = $request->sale_transaction['customer_id'];

            $sale_transaction->finalized_by = auth()->user()->id;

            $sale_transaction->finalized_at = Carbon::now();

            $sale_transaction->save();


            $amount = 0;

            foreach($request->sale_variations as $entry)
            {
                $purchase_variation = PurchaseVariation::find($entry['purchase_variation_id']);

                if($entry['quantity'] < 1 || $entry['quantity'] > $purchase_variation->quantity_available)
                {
                    DB::rollBack();

                    $value = $purchase_variation->serial != null ? $purchase_variation->serial : $purchase_variation->group;

                    return response([
                        'errors' => [
                            'message' => ['Sale quantity cannot be less than 1 or greater than available quantity !. ' . $value . ' is not available !']
                        ]
                    ], 409);
                }

                // ADJUSTING THE QUANTITY OF THE PURCHASE VARIATION RELATED TO THIS SALE VARIATION
                $purchase_variation->quantity_available -= $entry['quantity'];

                $purchase_variation->quantity_sold += $entry['quantity'];

                $purchase_variation->save();


                $sale_variation = new SaleVariation();

                $sale_variation->sale_transaction_id = $sale_transaction->id;

                $sale_variation->product_id = $entry['product_id'];

                $sale_variation->purchase_variation_id = $entry['purchase_variation_id'];

                $sale_variation->quantity = $entry['quantity'];

                $sale_variation->selling_price = $entry['selling_price'];

                $sale_variation->purchase_price = $purchase_variation->purchase_price + $purchase_variation->overhead_charge;

                $sale_variation->save();


                $amount += ($entry['selling_price'] * $entry['quantity']);
            }


            $sale_transaction->invoice_no = "Sale#" . ($sale_transaction->id + 1000);

            $sale_transaction->amount += $amount;

            $sale_transaction->save();


            // SALE INVOICE ENTRY FOR CUSTOMER ACCOUNT STATEMENT
            $cas_data_arr = [
                'type' => 'Invoice',
                'reference_id' => $sale_transaction->id,
                'amount' => $sale_transaction->amount,
                'customer_id' => $sale_transaction->customer_id
            ];

            CAS::store($cas_data_arr);


            // RECORD ENTRY FOR SALE TRANSACTION
            $rec_data_arr = [
                'category' => 'Transaction',
                'type' => 'Sale',
                'reference_id' => $sale_transaction->id,
                'cash_flow' => null,
                'amount' => $sale_transaction->amount
            ];

            REC::store($rec_data_arr);


            DB::commit();

            return response(['sale_transaction' => $sale_transaction], 201);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function imeiScan(Request $request)
    {
        if(!auth()->user()->hasPermission("sale.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->imei))
        {
            return response(['message' => 'Nothing to scan !'], 404);
        }

        $purchase_variation = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'purchase_variations.id',
                'p.id as product_id',
                'p.name',
                'p.sku',
                'purchase_variations.serial as imei',
                'purchase_variations.quantity_available',
                'purchase_variations.purchase_price',
                'purchase_variations.overhead_charge'

            )->where('purchase_variations.serial', '=', $request->imei)
            ->where('purchase_variations.quantity_available', '>', 0)
            ->first();
        
        if($purchase_variation == null)
        {
            return response(['message' => 'Not Available !'], 404);
        }

        return response(['purchase_variation' => $purchase_variation], 200);
    }

    public function imeiScanAlternative(Request $request)
    {
        if(!auth()->user()->hasPermission("sale.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->purchase_variation_id))
        {
            return response(['message' => 'Purchase Variation not specified !'], 404);
        }

        $purchase_variation = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'purchase_variations.id',
                'p.id as product_id',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),
                DB::raw('IF(purchase_variations.group is null, "N/A", purchase_variations.group) as "group"'),
                'purchase_variations.quantity_available',
                'purchase_variations.purchase_price',
                'purchase_variations.overhead_charge'

            )->where('purchase_variations.id', '=', $request->purchase_variation_id)
            ->where('purchase_variations.quantity_available', '>', 0)
            ->first();
        
        if($purchase_variation == null)
        {
            return response(['message' => 'Not Available !'], 404);
        }

        return response(['purchase_variation' => $purchase_variation], 200);
    }

    public function purchaseVariationsForSale(Request $request)
    {
        if(!auth()->user()->hasPermission("sale.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->product_model_id) && empty($request->product_id))
        {
            return response(['purchase_variations' => []], 200);
        }

        $purchase_variations = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'purchase_variations.id',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),
                DB::raw('IF(purchase_variations.group is null, "N/A", purchase_variations.group) as "group"'),
                'purchase_variations.quantity_available'

            )->where('purchase_variations.quantity_available', '>', 0);
            
        if(!empty($request->product_model_id))
        {
            $purchase_variations->where('p.product_model_id', '=', $request->product_model_id);
        }
        
        if(!empty($request->product_id))
        {
            $purchase_variations->where('p.id', '=', $request->product_id);
        }
        
        return response(['purchase_variations' => $purchase_variations->get()], 200);
    }

    public function getSaleVariations($sale_transaction_id)
    {
        if(!auth()->user()->hasPermission("sale.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_transaction = SaleTransaction::find($sale_transaction_id);

        if($sale_transaction == null)
        {
            return response(['message' => 'Sale Transaction not found !'], 404);
        }

        $sale_variations = SaleVariation::join('sale_transactions as st', 'st.id', '=', 'sale_variations.sale_transaction_id')
            ->join('products as p', 'p.id', '=', 'sale_variations.product_id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'sale_variations.purchase_variation_id')
            ->select(

                'sale_variations.id',
                'st.invoice_no',
                'p.name',
                'p.sku',
                DB::raw('IF(pv.serial is null, "N/A", pv.serial) as imei'),
                DB::raw('IF(pv.group is null, "N/A", pv.group) as "group"'),
                DB::raw('sale_variations.quantity - sale_variations.return_quantity as quantity'),
                'sale_variations.selling_price',
                'sale_variations.purchase_price'

            )->where('sale_variations.sale_transaction_id', '=', $sale_transaction_id)
            ->where(DB::raw('sale_variations.quantity - sale_variations.return_quantity'), '>', 0)
            ->get();

        return response(['sale_variations' => $sale_variations], 200);
    }

    public function storeSaleView()
    {
        if(!in_array("super_admin", auth()->user()->getRoles()) && auth()->user()->hasPermission("user.cua-enable"))
        {
            $customer_ids = auth()->user()->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->whereIn('id', $customer_ids)->orderBy('first_name', 'asc')->get();
        }
        else
        {
            $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->orderBy('first_name', 'asc')->get();
        }

        $product_models = ProductModel::select(['id', 'name'])->orderBy('name', 'asc')->get();

        $products = Product::select(['id', 'name', 'sku'])->orderBy('sku', 'asc')->get();

        return response([
            'customers' => $customers,
            'product_models' => $product_models,
            'products' => $products
        ], 200);
    }

    public function getSaleInvoice($sale_transaction_id, $json = true)
    {
        // THE FOLLOWING IS DISABLED TEMPORARILY

        // if(!auth()->user()->hasPermission("sale.index"))
        // {
        //     return response(['message' => 'Permission Denied !'], 403);
        // }

        $sale_transaction = SaleTransaction::find($sale_transaction_id);

        if($sale_transaction == null)
        {
            return response(['message' => 'Sale Transaction not found !'], 404);
        }


        $sale_transaction = SaleTransaction::join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->join('user_details as ud', 'ud.user_id', '=', 'u.id')
            ->leftJoin('payments as p', function($query) {

                $query->on('p.transaction_id', '=', 'sale_transactions.id')
                    ->where('p.payment_for', '=', 'sale');

            })
            ->select(

                'sale_transactions.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('CONCAT_WS(", ", ud.address, ud.city, ud.state, ud.country) as billing_address'),
                'sale_transactions.invoice_no',
                DB::raw('DATE_FORMAT(sale_transactions.transaction_date, "%m/%d/%Y") as date'),
                'sale_transactions.payment_status',
                'sale_transactions.amount as total',
                DB::raw('(select COUNT(invoice_no) from sale_return_transactions where sale_transaction_id = sale_transactions.id) as sale_return'),
                DB::raw('sale_transactions.amount - IFNULL((select SUM(amount) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0) as total_payable_after_sale_return'),
                DB::raw('IFNULL(SUM(p.amount), 0) as paid'),
                DB::raw('IFNULL((select SUM(amount_credited) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0) as amount_credited')

            )->where('sale_transactions.id', '=', $sale_transaction_id)
            ->first();


        $payments = Payment::join('payment_methods as pm', 'pm.id', '=', 'payments.payment_method_id')
            ->select(

                'payments.id',
                DB::raw('DATE_FORMAT(payments.payment_date, "%m/%d/%Y") as date'),
                'payments.payment_no',
                'payments.amount',
                'pm.name as payment_method',
                'payments.payment_note',

            )->where('payments.payment_for', '=', 'sale')
            ->where('payments.transaction_id', '=', $sale_transaction_id)
            ->orderBy('payments.payment_date', 'desc')
            ->orderBy('payments.payment_no', 'desc')
            ->get();


        $product_summary = SaleVariation::join('products as p', 'p.id', '=', 'sale_variations.product_id')
            ->select(

                'p.name',
                DB::raw('SUM(sale_variations.quantity - sale_variations.return_quantity) as quantity'),
                'sale_variations.selling_price as unit_price',

            )->where('sale_variations.sale_transaction_id', '=', $sale_transaction_id)
            ->where(DB::raw('sale_variations.quantity - sale_variations.return_quantity'), '>', 0)
            ->groupBy('sale_variations.selling_price')
            ->groupBy('sale_variations.product_id')
            ->orderBy('p.name', 'asc')
            ->get();


        $serial_list = SaleVariation::join('products as p', 'p.id', '=', 'sale_variations.product_id')
            ->join('product_models as pm', 'pm.id', '=', 'p.product_model_id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'sale_variations.purchase_variation_id')
            ->select(

                'sale_variations.id',
                'pm.name',
                'pv.serial as imei',
                'p.color',
                // 'p.ram', NOT NEEDED FOR NOW
                'p.storage',
                'p.condition'

            )->where('sale_variations.sale_transaction_id', '=', $sale_transaction_id)
            ->where('pv.serial', '<>', null)
            ->where(DB::raw('sale_variations.quantity - sale_variations.return_quantity'), '>', 0)
            ->orderBy('pm.name', 'asc')
            ->get();

        
        if($json)
        {
            return response([
                'sale_transaction' => $sale_transaction,
                'payments' => $payments,
                'product_summary' => $product_summary,
                'serial_list' => $serial_list
            ], 200);
        }
        else
        {
            return [
                'sale_transaction' => $sale_transaction,
                'payments' => $payments,
                'product_summary' => $product_summary,
                'serial_list' => $serial_list
            ];
        }
    }

    public function downloadSaleInvoice($sale_transaction_id)
    {
        // THE FOLLOWING IS DISABLED TEMPORARILY

        // if(!auth()->user()->hasPermission("sale.index"))
        // {
        //     return response(['message' => 'Permission Denied !'], 403);
        // }

        $data = $this->getSaleInvoice($sale_transaction_id, false);

        try {

            $pdf = PDF::loadView('sale.sale_invoice', $data);

            return $pdf->download('SaleInvoice_' . $data['sale_transaction']->invoice_no . '.pdf');

        } catch(Exception $ex) {

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function emailSaleInvoice($sale_transaction_id)
    {
        if(!auth()->user()->hasPermission("sale.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_transaction = SaleTransaction::find($sale_transaction_id);

        $data['subject'] = "Sale Invoice (" . $sale_transaction->invoice_no . ")";
        $data['email'] = $sale_transaction->customer->email;
        $data['name'] = $sale_transaction->customer->first_name . " " . $sale_transaction->customer->last_name; 
        $data['business_name'] = $sale_transaction->customer->userDetail->business_name;
        $data['invoice_no'] = $sale_transaction->invoice_no;
        $data['total'] =  $sale_transaction->amount - $sale_transaction->saleReturnTransactions->sum('amount');
        $data['payment_status'] = $sale_transaction->payment_status;

        $sale_invoice = $this->getSaleInvoice($sale_transaction_id, false);

        try {

            $data['pdf'] = PDF::loadView('sale.sale_invoice', $sale_invoice);

            Mail::send('email.sale_invoice', $data, function($message) use ($data) {

                $message->to($data['email'])
                    ->subject($data["subject"])
                    ->attachData($data['pdf']->output(), 'SaleInvoice_' . $data['invoice_no'] . '.pdf', ['mime'=>'application/pdf']);

            });

            return response([
                'messages' => [
                    'sale_invoice_email' => ['Sale Invoice Sent Successfully !']
                ]
            ], 200);

        } catch(Exception $ex) {

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function verification(Request $request)
    {
        if(!auth()->user()->hasPermission("sale.verification"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'sale_transaction_id' => 'required | numeric',
            'verification_status' => 'required | numeric',
            'verification_note' => 'string | nullable',
            'pin_number' => 'required | numeric'
        ], [
            'sale_transaction_id.required' => 'Sale Transaction ID is required',
            'sale_transaction_id.numeric' => 'Sale Transaction ID should be numeric',

            'verification_status.required' => 'Please specify the verification status !',
            'verification_status.numeric' => 'Verification Status should be numeric !',

            'verification_note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'pin_number.required' => 'Please enter your PIN Number !',
            'pin_number.numeric' => 'PIN Number should be numeric !'
        ]);


        if(auth()->user()->pin_number != $request->pin_number)
        {
            return response([
                'errors' => [
                    'pin_number' => ['Invalid Pin Number !']
                ]
            ], 409);
        }


        DB::beginTransaction();

        try {

            $sale_transaction = SaleTransaction::find($request->sale_transaction_id);

            $sale_transaction->verification_status = $request->verification_status;

            $sale_transaction->verification_note = $request->verification_note;

            $sale_transaction->verified_by = auth()->user()->id;

            $sale_transaction->verified_at = Carbon::now();

            $sale_transaction->save();


            // RECORD ENTRY FOR SALE VERIFICATION
            $rec_data_arr = [
                'type' => 'Sale',
                'reference_id' => $sale_transaction->id,
                'verified_by' => $sale_transaction->verified_by,
                'verified_at' => $sale_transaction->verified_at
            ];
            
            REC::storeVerificationRecord($rec_data_arr);


            DB::commit();

            return response([
                'sale_transaction_id' => $sale_transaction->id,
                'verification_status' => $request->verification_status,
                'verification_note' => $request->verification_note,
                'verified_by' => auth()->user()->first_name . " " . auth()->user()->last_name . " " . date_format(date_create($sale_transaction->verified_at), "m/d/Y H:i:s") 
            ], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    
}
