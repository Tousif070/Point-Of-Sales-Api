<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseVariation;
use App\Models\Payment;
use App\Models\User;
use REC;
use DB;
use Exception;
use Carbon\Carbon;

class PurchaseTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("purchase.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_transactions = PurchaseTransaction::join('users as u', 'u.id', '=', 'purchase_transactions.finalized_by')
            ->join('users as u2', 'u2.id', '=', 'purchase_transactions.supplier_id')
            ->leftJoin('users as u3', 'u3.id', '=', 'purchase_transactions.verified_by')
            ->leftJoin('purchase_variations as pv', 'pv.purchase_transaction_id', '=', 'purchase_transactions.id')
            ->select(

                'purchase_transactions.id',
                'purchase_transactions.verification_status',
                'purchase_transactions.verification_note',
                DB::raw('DATE_FORMAT(purchase_transactions.transaction_date, "%m/%d/%Y") as date'),
                'purchase_transactions.reference_no',
                DB::raw('CONCAT_WS(" ", u2.first_name, u2.last_name) as supplier'),
                'purchase_transactions.purchase_status',
                DB::raw('IF(SUM(pv.quantity_purchased) is null, 0, SUM(pv.quantity_purchased)) as total_items'),
                'purchase_transactions.locked',
                'purchase_transactions.payment_status',
                'purchase_transactions.amount',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(purchase_transactions.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by'),
                DB::raw('CONCAT_WS(" ", u3.first_name, DATE_FORMAT(purchase_transactions.verified_at, "%m/%d/%Y %H:%i:%s")) as verified_by')

            )->groupBy('purchase_transactions.id')
            ->orderBy('purchase_transactions.transaction_date', 'desc')
            ->orderBy('purchase_transactions.reference_no', 'desc')
            ->get();

        return response(['purchase_transactions' => $purchase_transactions], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("purchase.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'purchase_status' => 'required | string',
            'transaction_date' => 'required | date',
            'supplier_id' => 'required | numeric'
        ], [
            'purchase_status.required' => 'Please select the purchase status !',
            'purchase_status.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'transaction_date.required' => 'Please specify the transaction date !',
            'transaction_date.date' => 'Please specify a valid date !',

            'supplier_id.required' => 'Please select the supplier !',
            'supplier_id.numeric' => 'Supplier ID should be numeric !'
        ]);

        DB::beginTransaction();

        try {

            $purchase_transaction = new PurchaseTransaction();

            $purchase_transaction->purchase_status = $request->purchase_status;

            $purchase_transaction->payment_status = "Due";

            $purchase_transaction->transaction_date = Carbon::parse($request->transaction_date);

            $purchase_transaction->supplier_id = $request->supplier_id;

            $purchase_transaction->finalized_by = auth()->user()->id;

            $purchase_transaction->finalized_at = Carbon::now();

            $purchase_transaction->save();


            $purchase_transaction->reference_no = "Purchase#" . ($purchase_transaction->id + 1000);

            $purchase_transaction->save();


            // RECORD ENTRY FOR PURCHASE TRANSACTION
            $rec_data_arr = [
                'category' => 'Transaction',
                'type' => 'Purchase',
                'reference_id' => $purchase_transaction->id,
                'cash_flow' => null,
                'amount' => null
            ];

            REC::store($rec_data_arr);


            DB::commit();

            return response(['purchase_transaction' => $purchase_transaction], 201);

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

    public function getPurchaseVariations($purchase_transaction_id)
    {
        if(!auth()->user()->hasPermission("purchase.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_transaction = PurchaseTransaction::find($purchase_transaction_id);

        if($purchase_transaction == null)
        {
            return response(['message' => 'Purchase Transaction not found !'], 404);
        }

        $purchase_variations = PurchaseVariation::join('purchase_transactions as pt', 'pt.id', '=', 'purchase_variations.purchase_transaction_id')
            ->join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'purchase_variations.id',
                'pt.reference_no as purchase_reference',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),
                DB::raw('IF(purchase_variations.group is null, "N/A", purchase_variations.group) as "group"'),
                'purchase_variations.quantity_purchased',
                'purchase_variations.quantity_available',
                'purchase_variations.quantity_sold',
                'purchase_variations.purchase_price',
                'purchase_variations.overhead_charge',
                'purchase_variations.risk_fund'

            )->where('purchase_variations.purchase_transaction_id', '=', $purchase_transaction_id)
            ->orderBy('purchase_variations.created_at', 'desc')
            ->get();

        return response(['purchase_variations' => $purchase_variations], 200);
    }

    public function storePurchaseView()
    {
        $suppliers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 3)->orderBy('first_name', 'asc')->get();

        return response([
            'suppliers' => $suppliers
        ], 200);
    }

    public function getPurchaseInvoice($purchase_transaction_id)
    {
        if(!auth()->user()->hasPermission("purchase.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_transaction = PurchaseTransaction::find($purchase_transaction_id);

        if($purchase_transaction == null)
        {
            return response(['message' => 'Purchase Transaction not found !'], 404);
        }


        $purchase_transaction = PurchaseTransaction::join('users as u', 'u.id', '=', 'purchase_transactions.supplier_id')
            ->leftJoin('payments as p', function($query) {

                $query->on('p.transaction_id', '=', 'purchase_transactions.id')
                    ->where('p.payment_for', '=', 'purchase');

            })
            ->select(

                'purchase_transactions.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as supplier'),
                'purchase_transactions.reference_no',
                DB::raw('DATE_FORMAT(purchase_transactions.transaction_date, "%m/%d/%Y") as date'),
                'purchase_transactions.payment_status',
                'purchase_transactions.amount as total',
                DB::raw('IFNULL(SUM(p.amount), 0) as paid')

            )->where('purchase_transactions.id', '=', $purchase_transaction_id)
            ->first();


        $payments = Payment::join('payment_methods as pm', 'pm.id', '=', 'payments.payment_method_id')
            ->select(

                'payments.id',
                DB::raw('DATE_FORMAT(payments.payment_date, "%m/%d/%Y") as date'),
                'payments.payment_no',
                'payments.amount',
                'pm.name as payment_method',
                'payments.payment_note',

            )->where('payments.payment_for', '=', 'purchase')
            ->where('payments.transaction_id', '=', $purchase_transaction_id)
            ->orderBy('payments.payment_date', 'desc')
            ->orderBy('payments.payment_no', 'desc')
            ->get();


        $product_summary = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'p.name',
                DB::raw('SUM(purchase_variations.quantity_purchased) as quantity'),
                'purchase_variations.purchase_price',
                'purchase_variations.overhead_charge'

            )->where('purchase_variations.purchase_transaction_id', '=', $purchase_transaction_id)
            ->groupBy('purchase_variations.purchase_price')
            ->groupBy('purchase_variations.product_id')
            ->orderBy('p.name', 'asc')
            ->get();


        $serial_list = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->join('product_models as pm', 'pm.id', '=', 'p.product_model_id')
            ->select(

                'purchase_variations.id',
                'pm.name',
                'purchase_variations.serial as imei',
                'p.color',
                'p.ram',
                'p.storage',
                'p.condition'

            )->where('purchase_variations.purchase_transaction_id', '=', $purchase_transaction_id)
            ->where('purchase_variations.serial', '<>', null)
            ->orderBy('pm.name', 'asc')
            ->get();


        $group_list = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->join('product_models as pm', 'pm.id', '=', 'p.product_model_id')
            ->select(

                'purchase_variations.id',
                'pm.name',
                'purchase_variations.group as group',
                'p.color',
                'p.wattage',
                'p.type',
                'p.condition'

            )->where('purchase_variations.purchase_transaction_id', '=', $purchase_transaction_id)
            ->where('purchase_variations.group', '<>', null)
            ->orderBy('pm.name', 'asc')
            ->get();


        return response([
            'purchase_transaction' => $purchase_transaction,
            'payments' => $payments,
            'product_summary' => $product_summary,
            'serial_list' => $serial_list,
            'group_list' => $group_list
        ], 200);
    }

    public function toggleLock($purchase_transaction_id)
    {
        if(!auth()->user()->hasPermission("purchase.lock"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_transaction = PurchaseTransaction::find($purchase_transaction_id);

        if($purchase_transaction == null)
        {
            return response(['message' => 'Purchase Transaction not found !'], 404);
        }

        if($purchase_transaction->locked == 1)
        {
            $purchase_transaction->locked = 0;
        }
        else
        {
            $purchase_transaction->locked = 1;
        }

        $purchase_transaction->save();

        return response(['value' => $purchase_transaction->locked], 200);
    }

    public function verification(Request $request)
    {
        if(!auth()->user()->hasPermission("purchase.verification"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'purchase_transaction_id' => 'required | numeric',
            'verification_status' => 'required | numeric',
            'verification_note' => 'string | nullable',
            'pin_number' => 'required | numeric'
        ], [
            'purchase_transaction_id.required' => 'Purchase Transaction ID is required',
            'purchase_transaction_id.numeric' => 'Purchase Transaction ID should be numeric',

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

            $purchase_transaction = PurchaseTransaction::find($request->purchase_transaction_id);

            $purchase_transaction->verification_status = $request->verification_status;

            $purchase_transaction->verification_note = $request->verification_note;

            $purchase_transaction->verified_by = auth()->user()->id;

            $purchase_transaction->verified_at = Carbon::now();

            $purchase_transaction->save();


            // RECORD ENTRY FOR PURCHASE VERIFICATION
            $rec_data_arr = [
                'type' => 'Purchase',
                'reference_id' => $purchase_transaction->id,
                'verified_by' => $purchase_transaction->verified_by,
                'verified_at' => $purchase_transaction->verified_at
            ];

            REC::storeVerificationRecord($rec_data_arr);


            DB::commit();

            return response([
                'purchase_transaction_id' => $purchase_transaction->id,
                'verification_status' => $request->verification_status,
                'verification_note' => $request->verification_note,
                'verified_by' => auth()->user()->first_name . " " . auth()->user()->last_name . " " . date_format(date_create($purchase_transaction->verified_at), "m/d/Y H:i:s") 
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
