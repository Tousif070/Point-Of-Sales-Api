<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseTransaction;
use App\Models\Product;
use App\Models\PurchaseVariation;
use App\Models\SaleVariation;
use App\Models\SaleReturnVariation;
use App\Models\SkuTransfer;
use DB;
use Exception;

class SkuTransferController extends Controller
{
    public function view()
    {
        $purchase_references = PurchaseTransaction::select(['id', 'reference_no'])->orderBy('reference_no', 'asc')->get();

        $products = Product::select(['id', 'name', 'sku'])->orderBy('sku', 'asc')->get();

        return response([
            'purchase_references' => $purchase_references,
            'products' => $products
        ], 200);
    }

    public function storeView(Request $request)
    {
        if(!auth()->user()->hasPermission("sku-transfer.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->purchase_transaction_id))
        {
            return response(['purchase_variations' => []], 200);
        }

        $purchase_variations = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->join('purchase_transactions as pt', 'pt.id', '=', 'purchase_variations.purchase_transaction_id')
            ->select(

                'purchase_variations.id',
                'pt.reference_no as purchase_reference',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei')

            )->where('pt.id', '=', $request->purchase_transaction_id)
            ->orderBy('p.name', 'asc')
            ->get();

        return response(['purchase_variations' => $purchase_variations], 200);
    }

    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("sku-transfer.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'purchase_variation_ids' => 'required | array',
            'product_id' => 'required | numeric'
        ], [
            'purchase_variation_ids.required' => 'Please specify the purchase variations !',
            'purchase_variation_ids.array' => 'Purchase Variation IDs should be in an array !',

            'product_id.required' => 'Please select the SKU !',
            'product_id.numeric' => 'Product ID should be numeric !'
        ]);


        $flag = true;

        $batch_no = null;


        DB::beginTransaction();

        try {

            foreach($request->purchase_variation_ids as $purchase_variation_id)
            {
                $purchase_variation = PurchaseVariation::find($purchase_variation_id);

                
                $sku_transfer = new SkuTransfer();

                $sku_transfer->purchase_variation_id = $purchase_variation_id;

                $sku_transfer->previous_product_id = $purchase_variation->product_id;

                $sku_transfer->current_product_id = $request->product_id;

                $sku_transfer->save();


                if($flag)
                {
                    $batch_no = $sku_transfer->id + 100;

                    $flag = false;
                }


                $sku_transfer->batch_no = $batch_no;

                $sku_transfer->save();


                $purchase_variation->product_id = $request->product_id;

                $purchase_variation->save();


                $sale_variations = SaleVariation::where('purchase_variation_id', '=', $purchase_variation_id)->get();

                if(count($sale_variations) > 0)
                {
                    foreach($sale_variations as $sale_variation)
                    {
                        $sale_variation->product_id = $request->product_id;

                        $sale_variation->save();
                    }
                }


                $sale_return_variations = SaleReturnVariation::where('purchase_variation_id', '=', $purchase_variation_id)->get();

                if(count($sale_return_variations) > 0)
                {
                    foreach($sale_return_variations as $sale_return_variation)
                    {
                        $sale_return_variation->product_id = $request->product_id;

                        $sale_return_variation->save();
                    }
                }
            }

            
            DB::commit();

            return response(['message' => 'SKU Transfer Complete !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function history()
    {
        if(!auth()->user()->hasPermission("sku-transfer.history"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $history = SkuTransfer::select(

            DB::raw('DATE_FORMAT(created_at, "%m/%d/%Y") as date'),
            'batch_no',
            DB::raw('COUNT(id) as total')

        )->groupBy('batch_no')
        ->orderBy('batch_no', 'desc')
        ->get();

        return response(['history' => $history], 200);
    }

    public function historyDetails($batch_no)
    {
        if(!auth()->user()->hasPermission("sku-transfer.history"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $history_details = PurchaseVariation::join('purchase_transactions as pt', 'pt.id', '=', 'purchase_variations.purchase_transaction_id')
            ->join('sku_transfers as skt', 'skt.purchase_variation_id', '=', 'purchase_variations.id')
            ->join('products as p', 'p.id', '=', 'skt.previous_product_id')
            ->join('products as p2', 'p2.id', '=', 'skt.current_product_id')
            ->select(

                'purchase_variations.id',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),
                'pt.reference_no as purchase_reference',
                'p.name as previous_product_name',
                'p.sku as previous_sku',
                'p2.name as current_product_name',
                'p2.sku as current_sku'

            )->where('skt.batch_no', '=', $batch_no)
            ->get();

        return response(['history_details' => $history_details]);
    }


}
