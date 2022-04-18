<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\SaleVariation;
use DB;
use Exception;
use Carbon\Carbon;

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

        $sale_transactions = SaleTransaction::all();

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

        // VALIDATION WILL BE ADDED HERE

        DB::beginTransaction();

        try {

            $sale_transaction = new SaleTransaction();

            $sale_transaction->status = "Final";

            $sale_transaction->payment_status = "Due";

            $sale_transaction->transaction_date = Carbon::parse($request->sale_transaction->transaction_date);
            // CHANGE CONTACT_ID TO CUSTOMER_ID
            $sale_transaction->contact_id = $request->sale_transaction->customer_id;

            $sale_transaction->finalized_by = auth()->user()->id;

            $sale_transaction->finalized_at = Carbon::now();

            $sale_transaction->save();


            $amount = 0;

            foreach($request->sale_variations as $entry)
            {
                $sale_variation = new SaleVariation();

                $sale_variation->sale_transaction_id = $sale_transaction->id;

                $sale_variation->product_id = $entry->product_id;

                $sale_variation->purchase_variation_id = $entry->purchase_variation_id;

                $sale_variation->quantity = $entry->quantity;

                $sale_variation->unit_price = $entry->unit_price;

                $amount += $entry->unit_price;

                $sale_variation->save();
            }


            $sale_transaction->invoice_no = "Sale#" . ($sale_transaction->id + 1000);

            $sale_transaction->amount += $amount;

            $sale_transaction->save();


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
}
