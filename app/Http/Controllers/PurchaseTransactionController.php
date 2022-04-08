<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseTransaction;
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

        $purchase_transactions = PurchaseTransaction::all();

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
}
