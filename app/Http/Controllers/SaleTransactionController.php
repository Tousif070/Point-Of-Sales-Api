<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\SaleVariation;
use App\Models\PurchaseVariation;
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

        $request->validate([
            'sale_transaction.transaction_date' => 'required | date',
            'sale_transaction.customer_id' => 'required | numeric',

            'sale_variations.*.product_id' => 'required | numeric',
            'sale_variations.*.purchase_variation_id' => 'required | numeric',
            'sale_variations.*.quantity' => 'required | numeric',
            'sale_variations.*.unit_price' => 'required | numeric'
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

            'sale_variations.*.unit_price.required' => 'Selling Price is required !',
            'sale_variations.*.unit_price.numeric' => 'Selling Price should be numeric !'
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
                $sale_variation = new SaleVariation();

                $sale_variation->sale_transaction_id = $sale_transaction->id;

                $sale_variation->product_id = $entry['product_id'];

                $sale_variation->purchase_variation_id = $entry['purchase_variation_id'];

                $sale_variation->quantity = $entry['quantity'];

                $sale_variation->unit_price = $entry['unit_price'];

                // ADJUSTING THE QUANTITY OF THE PURCHASE VARIATION RELATED TO THIS SALE VARIATION
                $purchase_variation = PurchaseVariation::find($entry['purchase_variation_id']);

                $purchase_variation->quantity_available -= $entry['quantity'];

                $purchase_variation->quantity_sold += $entry['quantity'];

                $purchase_variation->save();


                $amount += $entry['unit_price'];

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

    public function imeiScan(Request $request)
    {
        if(!auth()->user()->hasPermission("sale.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_variation = PurchaseVariation::where('imei', '=', $request->imei)
            ->where('quantity_available', '>', 0)
            ->first();
        
        if($purchase_variation == null)
        {
            return response(['message' => 'Not Available !'], 404);
        }

        return response(['purchase_variation' => $purchase_variation], 200);
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

        return response(['sale_variations' => $sale_transaction->saleVariations], 200);
    }


}
