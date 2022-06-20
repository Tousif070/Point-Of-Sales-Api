<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseVariation;
use App\Models\User;
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
            ->select(

                'purchase_transactions.id',
                DB::raw('DATE_FORMAT(purchase_transactions.transaction_date, "%m/%d/%Y") as date'),
                'purchase_transactions.reference_no',
                DB::raw('CONCAT_WS(" ", u2.first_name, u2.last_name) as supplier'),
                'purchase_transactions.purchase_status',
                DB::raw(
                    'IF(
                        (select SUM(quantity_purchased) from purchase_variations where purchase_transaction_id = purchase_transactions.id) is null, 0, (select SUM(quantity_purchased) from purchase_variations where purchase_transaction_id = purchase_transactions.id)
                    ) as total_items'
                ),
                'purchase_transactions.payment_status',
                'purchase_transactions.amount',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(purchase_transactions.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->groupBy('purchase_transactions.id')
            ->orderBy('purchase_transactions.transaction_date', 'desc')
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
            'reference_no' => 'required | string',
            'purchase_status' => 'required | string',
            'transaction_date' => 'required | date',
            'supplier_id' => 'required | numeric'
        ], [
            'reference_no.required' => 'Please enter a reference !',
            'reference_no.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

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

            $purchase_transaction->reference_no = $request->reference_no;

            $purchase_transaction->purchase_status = $request->purchase_status;

            $purchase_transaction->payment_status = "Due";

            $purchase_transaction->transaction_date = Carbon::parse($request->transaction_date);

            $purchase_transaction->supplier_id = $request->supplier_id;

            $purchase_transaction->finalized_by = auth()->user()->id;

            $purchase_transaction->finalized_at = Carbon::now();

            $purchase_transaction->save();

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
                'purchase_variations.quantity_purchased',
                'purchase_variations.quantity_available',
                'purchase_variations.quantity_sold',
                'purchase_variations.purchase_price',
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


}
