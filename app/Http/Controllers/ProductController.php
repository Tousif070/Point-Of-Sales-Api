<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
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

        $products = Product::all();

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
            'name' => 'required | string',
            'brand_id' => 'required | numeric',
            'product_category_id' => 'required | numeric',
            'image' => 'required | image | max:2048',
            'color' => 'required | string',
            'ram' => 'required | numeric',
            'storage' => 'required | numeric',
            'condition' => 'required | string',
            'size' => 'required | string',
        ], [
            'name.required' => 'Please enter the name !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed !',

            'brand_id.required' => 'Please select the brand !',
            'brand_id.numeric' => 'Brand ID should be numeric !',

            'product_category_id.required' => 'Please select the product category !',
            'product_category_id.numeric' => 'Product Category ID should be numeric',

            'image.required' => 'Please upload an image !',
            'image.image' => 'Please upload an image file !',
            'image.max' => 'Maximum size limit is 2 MB !',

            'color.required' => 'Please enter the color !',
            'color.string' => 'Only alphabets, numbers & special characters are allowed !',

            'ram.required' => 'Please enter the ram allocation !',
            'ram.numeric' => 'The value should be numeric !',

            'storage.required' => 'Please enter the storage !',
            'storage.numeric' => 'The value should be numeric !',

            'condition.required' => 'Please enter the condition of the product !',
            'condition.string' => 'Only alphabets, numbers & special characters are allowed !',

            'size.required' => 'Please enter the display size !',
            'size.string' => 'Only alphabets, numbers & special characters are allowed !'
        ]);

        DB::beginTransaction();

        try {

            $product = new Product();

            $product->name = $request->name;

            $product->brand_id = $request->brand_id;

            $product->product_category_id = $request->product_category_id;


            // PRODUCT IMAGE
            $image_name = date('YmdHis') . "_" . mt_rand(1, 999999) . "." . $request->file('image')->getClientOriginalExtension();

            $image_path = $request->file('image')->storeAs('public/product_images', $image_name);

            $product->image = asset('public' . Storage::url($image_path));


            $product->color = $request->color;

            $product->ram = $request->ram;

            $product->storage = $request->storage;

            $product->condition = $request->condition;

            $product->size = $request->size;

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
}
