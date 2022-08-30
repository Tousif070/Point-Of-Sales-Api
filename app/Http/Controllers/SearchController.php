<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseVariation;
use DB;

class SearchController extends Controller
{
    public function imei(Request $request)
    {
        if(!auth()->user()->hasPermission("search.imei"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->imei))
        {
            return response([
                'errors' => [
                    'imei' => ['Please Enter IMEI !']
                    ]
                ], 409);
        }


        $basic_info = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
        ->join('users as ul', 'ul.id', '=', 'purchase_variations.belongs_to')
        ->select(
            
            'purchase_variations.id',
            'p.name as product_name',
            'p.sku',
            'purchase_variations.quantity_available',
            DB::raw('CONCAT_WS(" ", ul.first_name, ul.last_name) as location')
            
        )
        ->where('purchase_variations.serial', '=', $request->imei)
        ->get();

        
        $product_info = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
        ->join('product_categories as pc', 'pc.id', '=', 'p.product_category_id')
        ->join('product_models as pm', 'pm.id', '=', 'p.product_model_id')
        ->join('brands as b', 'b.id', '=', 'p.brand_id')
        ->join('files as f', 'f.id', '=', 'p.file_id')
        ->select(
        
            'purchase_variations.id',
            'p.name as product_name',
            'p.sku',
            'b.name as brand',
            'pc.name as category',
            'pm.name as model',
            'f.absolute_path as image'
        
        )
        ->where('purchase_variations.serial', '=', $request->imei)
        ->get();
        

        $purchase_info = PurchaseVariation::join('purchase_transactions as pt', 'pt.id', '=', 'purchase_variations.purchase_transaction_id')
        ->join('users as uf', 'uf.id', '=', 'pt.finalized_by')
        ->join('users as us', 'us.id', '=', 'pt.supplier_id')
        ->select(

            'purchase_variations.id',
            DB::raw('DATE_FORMAT(pt.transaction_date, "%m/%d/%Y") as date'),
            'pt.reference_no',
            DB::raw('CONCAT_WS(" ", us.first_name, us.last_name) as supplier'),
            'purchase_variations.quantity_purchased as quantity',
            DB::raw('(purchase_variations.purchase_price + purchase_variations.overhead_charge) as purchase_total'),
            DB::raw('CONCAT_WS(" ", uf.first_name, DATE_FORMAT(pt.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

        )
        ->where('purchase_variations.serial', '=', $request->imei)
        ->get();


        $sale_info = PurchaseVariation::join('sale_variations as sv', 'sv.purchase_variation_id', '=', 'purchase_variations.id')
        ->join('sale_transactions as st', 'st.id', '=', 'sv.sale_transaction_id')
        ->join('users as uc', 'uc.id', '=', 'st.customer_id')
        ->join('users as uf', 'uf.id', '=', 'st.finalized_by')
        ->select(

            'purchase_variations.id',
            DB::raw('DATE_FORMAT(st.transaction_date, "%m/%d/%Y") as date'),
            'st.invoice_no',
            DB::raw('CONCAT_WS(" ", uc.first_name, uc.last_name) as customer'),
            'sv.quantity',
            'sv.selling_price',
            DB::raw('CONCAT_WS(" ", uf.first_name, DATE_FORMAT(st.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

        )
        ->where('purchase_variations.serial', '=', $request->imei)
        ->orderBy('st.created_at', 'asc')
        ->get();


        $sale_return_info = PurchaseVariation::join('sale_return_variations as srv', 'srv.purchase_variation_id', '=', 'purchase_variations.id')
        ->join('sale_return_transactions as srt', 'srt.id', '=', 'srv.sale_return_transaction_id')
        ->join('sale_transactions as st', 'st.id', '=', 'srt.sale_transaction_id')
        ->join('users as uc', 'uc.id', '=', 'st.customer_id')
        ->join('users as uf', 'uf.id', '=', 'srt.finalized_by')
        ->select(

            'purchase_variations.id',
            DB::raw('DATE_FORMAT(srt.transaction_date, "%m/%d/%Y") as date'),
            'srt.invoice_no as return_invoice',
            'st.invoice_no as sale_invoice',
            DB::raw('CONCAT_WS(" ", uc.first_name, uc.last_name) as customer'),
            'srv.quantity',
            'srv.selling_price as return_price',
            DB::raw('CONCAT_WS(" ", uf.first_name, DATE_FORMAT(srt.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

        )
        ->where('purchase_variations.serial', '=', $request->imei)
        ->orderBy('srt.created_at', 'asc')
        ->get();


        $transfer_log_info = PurchaseVariation::join('product_transfers as pt', 'pt.purchase_variation_id', '=', 'purchase_variations.id')
        ->join('users as us', 'us.id', '=', 'pt.sender_id')
        ->join('users as ur', 'ur.id', '=', 'pt.receiver_id')
        ->select(

            'purchase_variations.id',
            DB::raw('DATE_FORMAT(pt.created_at, "%m/%d/%Y") as date'),
            'pt.batch_no',
            DB::raw('CONCAT_WS(" ", us.first_name, us.last_name) as sender'),
            DB::raw('CONCAT_WS(" ", ur.first_name, ur.last_name) as receiver')

        )
        ->where('purchase_variations.serial', '=', $request->imei)
        ->orderBy('pt.created_at', 'asc')
        ->get();


        return response([
            'basic_info' => $basic_info,
            'product_info' => $product_info,
            'purchase_info' => $purchase_info,
            'sale_info' => $sale_info,
            'sale_return_info' => $sale_return_info,
            'transfer_log_info' => $transfer_log_info
        ], 200);
    }


}
