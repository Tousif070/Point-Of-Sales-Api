<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\ProductModel;
use App\Models\File;
use Storage;
use DB;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("product.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $products = Product::join('brands as b', 'b.id', '=', 'products.brand_id')
            ->join('product_categories as pc', 'pc.id', '=', 'products.product_category_id')
            ->join('product_models as pm', 'pm.id', '=', 'products.product_model_id')
            ->join('files as f', 'f.id', '=', 'products.file_id')
            ->select(

                'products.id',
                'products.name',
                'b.name as brand',
                'pc.name as product_category',
                'pm.name as product_model',
                'products.sku',
                'f.absolute_path as image',
                'products.color',
                'products.ram',
                'products.storage',
                'products.condition',
                'products.size',
                'products.wattage',
                'products.type',
                'products.length'

            )->orderBy('products.name', 'asc')
            ->get();

        return response(['products' => $products], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePhone(Request $request)
    {
        if(!auth()->user()->hasPermission("product.store-phone"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        // THE FOLLOWING BLOCK OF CODE CAN ALSO BE USED FOR VALIDATION

        // $validator = Validator::make($request->all(), [
        //     'name' => 'required',
        //     'color' => 'required',
        //     'ram' => 'required | numeric',
        //     'storage' => 'required | numeric',
        //     'condition' => 'required'
        // ], [
        //     'name.required' => 'Please enter the name of the product !',
        //     'color.required' => 'Please enter the color of the product !',
        //     'ram.required' => 'Please enter the ram allocation of the product !',
        //     'ram.numeric' => 'The value should be numeric !',
        //     'storage.required' => 'Please enter the storage allocation of the product !',
        //     'storage.numeric' => 'The value should be numeric !',
        //     'condition.required' => 'Please enter the condition of the product !'
        // ]);

        // if($validator->fails())
        // {
        //     return $validator->errors();
        // }


        $request->validate([
            // 'name' => 'required | string', NOT NEEDED FOR NOW
            'brand_id' => 'required | numeric',
            // 'product_category_id' => 'required | numeric', NOT NEEDED FOR NOW
            'product_model_id' => 'required | numeric',
            'image' => 'required | image | max:2048',
            'color' => 'required | string',
            'ram' => 'required | numeric',
            'storage' => 'required | numeric',
            'condition' => 'required | string',
            'size' => 'required | string',
        ], [

            // THE FOLLOWING IS NOT NEEDED FOR NOW
            // 'name.required' => 'Please enter the name !',
            // 'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'brand_id.required' => 'Please select the brand !',
            'brand_id.numeric' => 'Brand ID should be numeric !',

            // THE FOLLOWING IS NOT NEEDED FOR NOW
            // 'product_category_id.required' => 'Please select the product category !',
            // 'product_category_id.numeric' => 'Product Category ID should be numeric !',

            'product_model_id.required' => 'Please select the product model !',
            'product_model_id.numeric' => 'Product Model ID should be numeric !',

            'image.required' => 'Please upload an image !',
            'image.image' => 'Please upload an image file !',
            'image.max' => 'Maximum size limit is 2 MB !',

            'color.required' => 'Please enter the color !',
            'color.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'ram.required' => 'Please enter the ram allocation !',
            'ram.numeric' => 'The value should be numeric !',

            'storage.required' => 'Please enter the storage !',
            'storage.numeric' => 'The value should be numeric !',

            'condition.required' => 'Please select the condition of the product !',
            'condition.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'size.required' => 'Please enter the display size !',
            'size.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        DB::beginTransaction();

        try {

            $product = new Product();

            $product->brand_id = $request->brand_id;

            // $product->product_category_id = $request->product_category_id; NOT NEEDED FOR NOW
            $product->product_category_id = 1;

            $product->product_model_id = $request->product_model_id;


            // PRODUCT IMAGE
            $image_name = date('YmdHis') . "_" . mt_rand(1, 999999) . "." . $request->file('image')->getClientOriginalExtension();

            $image_path = $request->file('image')->storeAs('public/product_images', $image_name);

            $absolute_path = asset('public' . Storage::url($image_path));

            $file = new File();

            $file->file_path = $image_path;

            $file->absolute_path = $absolute_path;

            $file->save();


            $product->file_id = $file->id;

            $product->color = $request->color;

            $product->ram = $request->ram;

            $product->storage = $request->storage;

            $product->condition = $request->condition;

            $product->size = $request->size . " Inch";

            $model_name = ProductModel::find($request->product_model_id)->name;

            // PRODUCT NAME STORED WITH A SPECIFIC FORMAT
            $product->name = $model_name . " " . $product->color . " " . $product->ram . "/" . $product->storage . " " . $product->condition;

            $product->save();

            $product->sku = $product->id + 2000;

            $product->save();

            DB::commit();

            return response(['product' => $product], 201);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCharger(Request $request)
    {
        if(!auth()->user()->hasPermission("product.store-charger"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            // 'name' => 'required | string', NOT NEEDED FOR NOW
            'brand_id' => 'required | numeric',
            // 'product_category_id' => 'required | numeric', NOT NEEDED FOR NOW
            'product_model_id' => 'required | numeric',
            'image' => 'required | image | max:2048',
            'color' => 'required | string',
            'condition' => 'required | string',
            'wattage' => 'required | string',
            'type' => 'required | string'
        ], [

            // THE FOLLOWING IS NOT NEEDED FOR NOW
            // 'name.required' => 'Please enter the name !',
            // 'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'brand_id.required' => 'Please select the brand !',
            'brand_id.numeric' => 'Brand ID should be numeric !',

            // THE FOLLOWING IS NOT NEEDED FOR NOW
            // 'product_category_id.required' => 'Please select the product category !',
            // 'product_category_id.numeric' => 'Product Category ID should be numeric',

            'product_model_id.required' => 'Please select the product model !',
            'product_model_id.numeric' => 'Product Model ID should be numeric !',

            'image.required' => 'Please upload an image !',
            'image.image' => 'Please upload an image file !',
            'image.max' => 'Maximum size limit is 2 MB !',

            'color.required' => 'Please enter the color !',
            'color.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'condition.required' => 'Please select the condition of the product !',
            'condition.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'wattage.required' => 'Please enter the charging wattage !',
            'wattage.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'type.required' => 'Please enter the charging type !',
            'type.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        DB::beginTransaction();

        try {

            $product = new Product();

            $product->brand_id = $request->brand_id;

            // $product->product_category_id = $request->product_category_id; NOT NEEDED FOR NOW
            $product->product_category_id = 2;

            $product->product_model_id = $request->product_model_id;


            // PRODUCT IMAGE
            $image_name = date('YmdHis') . "_" . mt_rand(1, 999999) . "." . $request->file('image')->getClientOriginalExtension();

            $image_path = $request->file('image')->storeAs('public/product_images', $image_name);

            $absolute_path = asset('public' . Storage::url($image_path));

            $file = new File();

            $file->file_path = $image_path;

            $file->absolute_path = $absolute_path;

            $file->save();


            $product->file_id = $file->id;

            $product->color = $request->color;

            $product->condition = $request->condition;

            $product->wattage = $request->wattage . "W";

            $product->type = $request->type;

            $model_name = ProductModel::find($request->product_model_id)->name;

            // PRODUCT NAME STORED WITH A SPECIFIC FORMAT
            $product->name = $model_name . " " . $product->color . " " . $product->wattage . "/" . $product->type . " " . $product->condition;

            $product->save();

            $product->sku = $product->id + 2000;

            $product->save();

            DB::commit();

            return response(['product' => $product], 201);

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

    public function storeProductView()
    {
        $brands = Brand::select(['id', 'name'])->orderBy('name', 'asc')->get();

        // THE FOLLOWING IS NOT NEEDED FOR NOW
        // $product_categories = ProductCategory::select(['id', 'name'])->get();

        $product_models = ProductModel::select(['id', 'name'])->orderBy('name', 'asc')->get();

        return response([
            'brands' => $brands,
            // 'product_categories' => $product_categories,
            'product_models' => $product_models
        ], 200);
    }

    public function getPurchaseVariations($product_id)
    {
        if(!auth()->user()->hasPermission("product.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $product = Product::find($product_id);

        if($product == null)
        {
            return response(['message' => 'Product not found !'], 404);
        }

        $purchase_variations = Product::join('purchase_variations as pv', 'pv.product_id', '=', 'products.id')
            ->select(

                'pv.id',
                'products.name',
                'products.sku',
                DB::raw('IF(pv.serial is null, "N/A", pv.serial) as imei'),
                'pv.quantity_purchased',
                'pv.quantity_available',
                'pv.quantity_sold',
                'pv.purchase_price',
                'pv.overhead_charge',
                'pv.risk_fund'

            )->where('products.id', '=', $product_id)
            ->where('pv.quantity_available', '>', 0)
            ->orderBy('pv.created_at', 'desc')
            ->get();

        return response(['purchase_variations' => $purchase_variations], 200);
    }


}
