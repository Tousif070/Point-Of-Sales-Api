<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleReturnTransaction;
use App\Models\SaleVariation;
use App\Models\PurchaseVariation;
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

        $sale_return_transactions = SaleReturnTransaction::all();

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
            'sale_variations.*.return_quantity' => 'required | numeric'
        ], [
            'sale_return_transaction.sale_transaction_id.required' => 'Sale Transaction ID is required !',
            'sale_return_transaction.sale_transaction_id.numeric' => 'Sale Transaction ID should be numeric !',

            'sale_return_transaction.transaction_date.required' => 'Please specify the transaction date !',
            'sale_return_transaction.transaction_date.date' => 'Please specify a valid date !',

            'sale_variations.*.sale_variation_id.required' => 'Sale Variation ID is required !',
            'sale_variations.*.sale_variation_id.numeric' => 'Sale Variation ID should be numeric !',

            'sale_variations.*.return_quantity.required' => 'Return Quantity is required !',
            'sale_variations.*.return_quantity.numeric' => 'Return Quantity should be numeric !'
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

                $sale_variation->return_quantity = $return['return_quantity'];

                $sale_variation->sale_return_transaction_id = $request->sale_return_transaction['sale_transaction_id'];

                // ADJUSTING THE QUANTITY OF THE PURCHASE VARIATION RELATED TO THIS SALE VARIATION
                $purchase_variation = PurchaseVariation::find($sale_variation->purchase_variation_id);

                $purchase_variation->quantity_available += $return['return_quantity'];

                $purchase_variation->quantity_sold -= $return['return_quantity'];

                $purchase_variation->save();


                $amount += $sale_variation->unit_price;

                $sale_variation->save();
            }


            $sale_return_transaction->invoice_no = "Return#" . ($sale_return_transaction->id + 1000);

            $sale_return_transaction->amount += $amount;

            $sale_return_transaction->save();


            DB::commit();

            return response(['sale_return_transaction' => $sale_return_transaction], 201);

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
