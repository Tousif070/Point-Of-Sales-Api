<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseVariation;
use App\Models\Product;
use DB;
use Exception;

class PurchaseVariationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("purchase-variation.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
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
                'purchase_variations.risk_fund'

            )->orderBy('purchase_variations.created_at', 'desc')
            ->get();

        return response(['purchase_variations' => $purchase_variations], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("purchase-variation.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'purchase_transaction_id' => 'required | numeric',
            'product_id' => 'required | numeric',
            'serial' => 'required | string | unique:purchase_variations,serial',
            'quantity_purchased' => 'required | numeric',
            'purchase_price' => 'required | numeric',
            'risk_fund' => 'required | numeric'
        ], [
            'purchase_transaction_id.required' => 'Purchase Transaction ID is required !',
            'purchase_transaction_id.numeric' => 'Purchase Transaction ID should be numeric !',

            'product_id.required' => 'Product ID is required !',
            'product_id.numeric' => 'Product ID should be numeric !',

            'serial.required' => 'serial is required !',
            'serial.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'serial.unique' => 'Duplicate serial !',

            'quantity_purchased.required' => 'Purchase quantity is required !',
            'quantity_purchased.numeric' => 'Purchase quantity should be numeric !',

            'purchase_price.required' => 'Purchase price is required !',
            'purchase_price.numeric' => 'Purchase price should be numeric !',

            'risk_fund.required' => 'Please set the risk fund !',
            'risk_fund.numeric' => 'Risk fund should be numeric !'
        ]);


        if($request->quantity_purchased < 1)
        {
            return response(['message' => 'Purchase quantity cannot be less than 1 !'], 409);
        }


        DB::beginTransaction();

        try {

            $purchase_variation = new PurchaseVariation();

            $purchase_variation->purchase_transaction_id = $request->purchase_transaction_id;

            $purchase_variation->product_id = $request->product_id;

            if($request->serial != "N/A")
            {
                $purchase_variation->serial = $request->serial;
            }

            $purchase_variation->quantity_purchased = $request->quantity_purchased;

            $purchase_variation->quantity_available = $request->quantity_purchased;

            $purchase_variation->purchase_price = $request->purchase_price;

            $purchase_variation->risk_fund = $request->risk_fund;

            $purchase_variation->save();


            if($request->serial == "N/A")
            {
                $purchase_variation->group = "G-" . ($purchase_variation->id + 100);

                $purchase_variation->save();
            }


            $purchase_transaction = PurchaseTransaction::find($purchase_variation->purchase_transaction_id);

            $purchase_transaction->amount += ($purchase_variation->purchase_price * $purchase_variation->quantity_purchased);

            $purchase_transaction->save();


            $product = Product::find($purchase_variation->product_id);


            $purchase_variation->purchase_reference = $purchase_transaction->reference_no;

            $purchase_variation->name = $product->name;

            $purchase_variation->sku = $product->sku;


            DB::commit();

            return response(['purchase_variation' => $purchase_variation], 201);

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

    public function getProductCategoryType($product_id)
    {
        if(!auth()->user()->hasPermission("purchase-variation.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $product = Product::find($product_id);

        if($product == null)
        {
            return response(['message' => 'Product not found !'], 404);
        }

        return response(['product_category_type' => $product->productCategory->type]);
    }

    public function getAveragePurchasePrice()
    {
        if(!auth()->user()->hasPermission("purchase-variation.avg-purchase-price"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $average_purchase_prices = Product::join('purchase_variations as pv', 'pv.product_id', '=', 'products.id')
            ->select(

                'products.id',
                'products.name',
                'products.sku',
                DB::raw("SUM(pv.quantity_available) as quantity_available"),
                DB::raw("ROUND(SUM(pv.quantity_available * pv.purchase_price) / SUM(pv.quantity_available), 2) as average_purchase_price")

            )->where('pv.quantity_available', '>', 0)
            ->groupBy('products.id')
            ->get();
        
        return response(['average_purchase_prices' => $average_purchase_prices], 200);
    }

    public function storePurchaseVariationView()
    {
        $products = Product::select(['id', 'name', 'sku'])->orderBy('sku', 'asc')->get();

        return response([
            'products' => $products
        ], 200);
    }


}
