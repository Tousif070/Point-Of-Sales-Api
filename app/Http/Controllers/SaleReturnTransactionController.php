<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\SaleReturnTransaction;
use App\Models\SaleVariation;
use App\Models\SaleReturnVariation;
use App\Models\PurchaseVariation;
use App\Models\User;
use App\Models\CustomerCredit;
use CAS;
use REC;
use DB;
use Exception;
use Carbon\Carbon;

class SaleReturnTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("sale-return.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_return_transactions = SaleReturnTransaction::join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
            ->join('users as u', 'u.id', '=', 'sale_return_transactions.finalized_by')
            ->join('users as u2', 'u2.id', '=', 'st.customer_id')
            ->leftJoin('users as u3', 'u3.id', '=', 'sale_return_transactions.verified_by')
            ->join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
            ->select(

                'sale_return_transactions.id',
                'sale_return_transactions.verification_status',
                'sale_return_transactions.verification_note',
                DB::raw('DATE_FORMAT(sale_return_transactions.transaction_date, "%m/%d/%Y") as date'),
                'sale_return_transactions.invoice_no as return_invoice',
                'st.invoice_no as sale_invoice',
                DB::raw('CONCAT_WS(" ", u2.first_name, u2.last_name) as customer'),
                DB::raw('SUM(srv.quantity) as total_items'),
                'sale_return_transactions.amount',
                'sale_return_transactions.amount_credited',
                DB::raw('sale_return_transactions.amount - sale_return_transactions.amount_credited as amount_adjusted'),
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(sale_return_transactions.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by'),
                DB::raw('CONCAT_WS(" ", u3.first_name, DATE_FORMAT(sale_return_transactions.verified_at, "%m/%d/%Y %H:%i:%s")) as verified_by')

            )->groupBy('sale_return_transactions.id')
            ->orderBy('sale_return_transactions.transaction_date', 'desc')
            ->orderBy('sale_return_transactions.invoice_no', 'desc')
            ->get();

        return response(['sale_return_transactions' => $sale_return_transactions], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("sale-return.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'sale_return_transaction.sale_transaction_id' => 'required | numeric',
            'sale_return_transaction.transaction_date' => 'required | date',

            'sale_variations.*.sale_variation_id' => 'required | numeric',
            'sale_variations.*.return_quantity' => 'required | numeric',
            
            'sale_variations.*.return_deduction' => 'nullable | numeric | min:0'
        ], [
            'sale_return_transaction.sale_transaction_id.required' => 'Sale Transaction ID is required !',
            'sale_return_transaction.sale_transaction_id.numeric' => 'Sale Transaction ID should be numeric !',

            'sale_return_transaction.transaction_date.required' => 'Please specify the transaction date !',
            'sale_return_transaction.transaction_date.date' => 'Please specify a valid date !',

            'sale_variations.*.sale_variation_id.required' => 'Sale Variation ID is required !',
            'sale_variations.*.sale_variation_id.numeric' => 'Sale Variation ID should be numeric !',

            'sale_variations.*.return_quantity.required' => 'Return Quantity is required !',
            'sale_variations.*.return_quantity.numeric' => 'Return Quantity should be numeric !',

            'sale_variations.*.return_deduction.numeric' => 'Return Deduction should be numeric !',
            'sale_variations.*.return_deduction.min' => 'Minimum value should be 0 !'
        ]);

        DB::beginTransaction();

        try {

            $sale_return_transaction = new SaleReturnTransaction();

            $sale_return_transaction->sale_transaction_id = $request->sale_return_transaction['sale_transaction_id'];

            $sale_return_transaction->transaction_date = Carbon::parse($request->sale_return_transaction['transaction_date']);

            $sale_return_transaction->finalized_by = auth()->user()->id;

            $sale_return_transaction->finalized_at = Carbon::now();

            $sale_return_transaction->save();


            $amount = 0;

            foreach($request->sale_variations as $return)
            {
                $sale_variation = SaleVariation::find($return['sale_variation_id']);

                if($return['return_quantity'] < 1 || $return['return_quantity'] > ($sale_variation->quantity - $sale_variation->return_quantity))
                {
                    DB::rollBack();

                    return response(['message' => 'Return quantity cannot be less than 1 or greater than returnable quantity !'], 409);
                }

                $sale_variation->return_quantity += $return['return_quantity'];

                $sale_variation->save();

                // ADJUSTING THE QUANTITY OF THE PURCHASE VARIATION RELATED TO THIS SALE VARIATION
                $purchase_variation = PurchaseVariation::find($sale_variation->purchase_variation_id);

                $purchase_variation->quantity_available += $return['return_quantity'];

                $purchase_variation->quantity_sold -= $return['return_quantity'];

                $purchase_variation->save();


                $sale_return_variation = new SaleReturnVariation();

                $sale_return_variation->sale_return_transaction_id = $sale_return_transaction->id;

                $sale_return_variation->product_id = $sale_variation->product_id;

                $sale_return_variation->purchase_variation_id = $sale_variation->purchase_variation_id;

                $sale_return_variation->quantity = $return['return_quantity'];

                $sale_return_variation->selling_price = $sale_variation->selling_price;

                $sale_return_variation->purchase_price = $sale_variation->purchase_price;

                $sale_return_variation->return_deduction = empty($return['return_deduction']) ? 0.00 : $return['return_deduction'];

                $sale_return_variation->save();


                $amount += (($sale_variation->selling_price - $sale_return_variation->return_deduction) * $return['return_quantity']);
            }


            $sale_return_transaction->invoice_no = "Return#" . ($sale_return_transaction->id + 1000);

            $sale_return_transaction->amount += $amount;

            $sale_return_transaction->save();


            $this->extras($sale_return_transaction);


            // SALE RETURN INVOICE ENTRY FOR CUSTOMER ACCOUNT STATEMENT
            $cas_data_arr = [
                'type' => 'Return',
                'reference_id' => $sale_return_transaction->id,
                'amount' => $sale_return_transaction->amount,
                'customer_id' => $sale_return_transaction->saleTransaction->customer_id
            ];

            CAS::store($cas_data_arr);


            // RECORD ENTRY FOR SALE RETURN TRANSACTION
            $rec_data_arr = [
                'category' => 'Transaction',
                'type' => 'Sale Return',
                'reference_id' => $sale_return_transaction->id,
                'cash_flow' => null,
                'amount' => $sale_return_transaction->amount
            ];

            REC::store($rec_data_arr);


            DB::commit();

            // return response(['sale_return_transaction' => $sale_return_transaction], 201); NOT NEEDED FOR NOW
            return response(['message' => "Sale Return Successful !"], 201);

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

    public function getSaleReturnVariations($sale_return_transaction_id)
    {
        if(!auth()->user()->hasPermission("sale-return.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_return_transaction = SaleReturnTransaction::find($sale_return_transaction_id);

        if($sale_return_transaction == null)
        {
            return response(['message' => 'Sale Return Transaction not found !'], 404);
        }

        $sale_return_variations = SaleReturnVariation::join('sale_return_transactions as srt', 'srt.id', '=', 'sale_return_variations.sale_return_transaction_id')
            ->join('products as p', 'p.id', '=', 'sale_return_variations.product_id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'sale_return_variations.purchase_variation_id')
            ->select(
                
                'sale_return_variations.id',
                'srt.invoice_no as return_invoice',
                'p.name',
                'p.sku',
                DB::raw('IF(pv.serial is null, "N/A", pv.serial) as imei'),
                DB::raw('IF(pv.group is null, "N/A", pv.group) as "group"'),
                'sale_return_variations.quantity',
                'sale_return_variations.selling_price',
                'sale_return_variations.purchase_price',
                'sale_return_variations.return_deduction'

            )->where('sale_return_variations.sale_return_transaction_id', '=', $sale_return_transaction_id)
            ->get();

        return response(['sale_return_variations' => $sale_return_variations], 200);
    }

    public function extras($srt)
    {
        $st = $srt->saleTransaction;

        // CHECKING FOR CUSTOMER CREDIT
        if($st->payment_status == "Paid")
        {
            $customer_credit = new CustomerCredit();

            $customer_credit->amount = $srt->amount;

            $customer_credit->type = "Sale Return";

            $customer_credit->sale_invoice = $st->invoice_no;

            $customer_credit->sale_return_invoice = $srt->invoice_no;

            $customer_credit->customer_id = $st->customer_id;

            $customer_credit->note = "From sale return";

            $customer_credit->finalized_by = auth()->user()->id;

            $customer_credit->finalized_at = Carbon::now();

            $customer_credit->save();


            $customer = User::find($st->customer_id);

            $customer->userDetail->available_credit += $srt->amount;

            $customer->userDetail->save();


            $srt->amount_credited = $srt->amount;

            $srt->save();
        }
        else
        {
            $total_paid = $st->payments()->where('payment_for', '=', 'sale')->sum('amount');
    
            $total_payable = $st->amount - $st->saleReturnTransactions->sum('amount');
    
            if($total_paid > $total_payable)
            {
                $credit_amount = $total_paid - $total_payable;
    
                $customer_credit = new CustomerCredit();
    
                $customer_credit->amount = $credit_amount;
    
                $customer_credit->type = "Sale Return";
    
                $customer_credit->sale_invoice = $st->invoice_no;
    
                $customer_credit->sale_return_invoice = $srt->invoice_no;
    
                $customer_credit->customer_id = $st->customer_id;
    
                $customer_credit->note = "From sale return";
    
                $customer_credit->finalized_by = auth()->user()->id;
    
                $customer_credit->finalized_at = Carbon::now();
    
                $customer_credit->save();
    
    
                $customer = User::find($st->customer_id);
    
                $customer->userDetail->available_credit += $credit_amount;
    
                $customer->userDetail->save();
    
    
                $srt->amount_credited = $credit_amount;
    
                $srt->save();
            }
        }


        // CHANGING PAYMENT STATUS IF NEEDED
        if($st->payment_status != "Paid" && $total_paid >= $total_payable)
        {
            $st->payment_status = "Paid";

            $st->save();
        }
    }

    public function storeSaleReturnView($sale_transaction_id)
    {
        if(!auth()->user()->hasPermission("sale-return.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_transaction = SaleTransaction::with(['customer'])->select(['invoice_no', 'customer_id'])->where('id', '=', $sale_transaction_id)->first();

        if($sale_transaction == null)
        {
            return response(['message' => 'Sale Transaction not found !'], 404);
        }

        $sale_variations = SaleVariation::join('products as p', 'p.id', '=', 'sale_variations.product_id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'sale_variations.purchase_variation_id')
            ->select(

                'sale_variations.id',
                'p.name',
                'p.sku',
                DB::raw('IF(pv.serial is null, "N/A", pv.serial) as imei'),
                DB::raw('IF(pv.group is null, "N/A", pv.group) as "group"'),
                'sale_variations.quantity as sale_quantity',
                'sale_variations.return_quantity as previously_returned',
                'sale_variations.selling_price as price'

            )->where('sale_variations.sale_transaction_id', '=', $sale_transaction_id)
            ->where(DB::raw('sale_variations.quantity - sale_variations.return_quantity'), '>', 0)
            ->get();

        return response([
            'sale_invoice' => $sale_transaction->invoice_no,
            'customer_name' => $sale_transaction->customer->first_name . " " . $sale_transaction->customer->last_name,
            'sale_variations' => $sale_variations
        ], 200);
    }

    public function getSaleReturnInvoice($sale_return_transaction_id)
    {
        if(!auth()->user()->hasPermission("sale-return.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_return_transaction = SaleReturnTransaction::find($sale_return_transaction_id);

        if($sale_return_transaction == null)
        {
            return response(['message' => 'Sale Return Transaction not found !'], 404);
        }


        $sale_return_transaction = SaleReturnTransaction::join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
            ->join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
            ->join('users as u', 'u.id', '=', 'st.customer_id')
            ->select(

                'sale_return_transactions.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                'sale_return_transactions.invoice_no',
                DB::raw('DATE_FORMAT(sale_return_transactions.transaction_date, "%m/%d/%Y") as date'),
                'st.invoice_no as parent_invoice',
                'sale_return_transactions.amount as total',
                DB::raw('SUM(srv.return_deduction * srv.quantity) as return_deduction'),
                'sale_return_transactions.amount_credited',
                DB::raw('sale_return_transactions.amount - sale_return_transactions.amount_credited as amount_adjusted')

            )->where('sale_return_transactions.id', '=', $sale_return_transaction_id)
            ->first();


        $product_summary = SaleReturnVariation::join('products as p', 'p.id', '=', 'sale_return_variations.product_id')
            ->select(

                'p.name',
                DB::raw('SUM(sale_return_variations.quantity) as quantity'),
                'sale_return_variations.selling_price as unit_price',

            )->where('sale_return_variations.sale_return_transaction_id', '=', $sale_return_transaction_id)
            ->groupBy('sale_return_variations.selling_price')
            ->groupBy('sale_return_variations.product_id')
            ->orderBy('p.name', 'asc')
            ->get();


        $serial_list = SaleReturnVariation::join('products as p', 'p.id', '=', 'sale_return_variations.product_id')
            ->join('product_models as pm', 'pm.id', '=', 'p.product_model_id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'sale_return_variations.purchase_variation_id')
            ->select(

                'sale_return_variations.id',
                'pm.name',
                'pv.serial as imei',
                'p.color',
                // 'p.ram', NOT NEEDED FOR NOW
                'p.storage',
                'p.condition'

            )->where('sale_return_variations.sale_return_transaction_id', '=', $sale_return_transaction_id)
            ->where('pv.serial', '<>', null)
            ->orderBy('pm.name', 'asc')
            ->get();


        return response([
            'sale_return_transaction' => $sale_return_transaction,
            'product_summary' => $product_summary,
            'serial_list' => $serial_list
        ], 200);
    }

    public function verification(Request $request)
    {
        if(!auth()->user()->hasPermission("sale-return.verification"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'sale_return_transaction_id' => 'required | numeric',
            'verification_status' => 'required | numeric',
            'verification_note' => 'string | nullable',
            'pin_number' => 'required | numeric'
        ], [
            'sale_return_transaction_id.required' => 'Sale Return Transaction ID is required',
            'sale_return_transaction_id.numeric' => 'Sale Return Transaction ID should be numeric',

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

            $sale_return_transaction = SaleReturnTransaction::find($request->sale_return_transaction_id);

            $sale_return_transaction->verification_status = $request->verification_status;

            $sale_return_transaction->verification_note = $request->verification_note;

            $sale_return_transaction->verified_by = auth()->user()->id;

            $sale_return_transaction->verified_at = Carbon::now();

            $sale_return_transaction->save();


            // RECORD ENTRY FOR SALE RETURN VERIFICATION
            $rec_data_arr = [
                'type' => 'Sale Return',
                'reference_id' => $sale_return_transaction->id,
                'verified_by' => $sale_return_transaction->verified_by,
                'verified_at' => $sale_return_transaction->verified_at
            ];
            
            REC::storeVerificationRecord($rec_data_arr);


            DB::commit();

            return response([
                'sale_return_transaction_id' => $sale_return_transaction->id,
                'verification_status' => $request->verification_status,
                'verification_note' => $request->verification_note,
                'verified_by' => auth()->user()->first_name . " " . auth()->user()->last_name . " " . date_format(date_create($sale_return_transaction->verified_at), "m/d/Y H:i:s") 
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
