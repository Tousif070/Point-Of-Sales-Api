<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return $products;
    }

    public function store(Request $request)
    {
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
            'name' => 'required',
            'color' => 'required',
            'ram' => 'required | numeric',
            'storage' => 'required | numeric',
            'condition' => 'required',
            'image' => 'required | image | max:2048'
        ], [
            'name.required' => 'Please enter the name of the product !',
            'color.required' => 'Please enter the color of the product !',
            'ram.required' => 'Please enter the ram allocation of the product !',
            'ram.numeric' => 'The value should be numeric !',
            'storage.required' => 'Please enter the storage allocation of the product !',
            'storage.numeric' => 'The value should be numeric !',
            'condition.required' => 'Please enter the condition of the product !',
            'image.required' => 'Please upload an image !',
            'image.image' => 'Please upload an image file !',
            'image.max' => 'Maximum size limit is 2 MB !'
        ]);

        $product = new Product();

        $product->name = $request->name;

        $product->color = $request->color;

        $product->ram = $request->ram;

        $product->storage = $request->storage;

        $product->condition = $request->condition;


        // PRODUCT IMAGE
        $image_name = date('YmdHis') . "_" . mt_rand(1, 999999) . "." . $request->file('image')->getClientOriginalExtension();

        $image_path = $request->file('image')->storeAs('public/product_images', $image_name);

        $product->image = asset('public' . Storage::url($image_path));


        $product->save();

        $product->sku = $product->id + 2000;

        $product->save();

        return response($product, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'color' => 'required',
            'ram' => 'required | numeric',
            'storage' => 'required | numeric',
            'condition' => 'required',
            'image' => 'image | max:2048'
        ], [
            'name.required' => 'Please enter the name of the product !',
            'color.required' => 'Please enter the color of the product !',
            'ram.required' => 'Please enter the ram allocation of the product !',
            'ram.numeric' => 'The value should be numeric !',
            'storage.required' => 'Please enter the storage allocation of the product !',
            'storage.numeric' => 'The value should be numeric !',
            'condition.required' => 'Please enter the condition of the product !',
            'image.image' => 'Please upload an image file !',
            'image.max' => 'Maximum size limit is 2 MB !'
        ]);

        $product = Product::find($id);

        if($product == null)
        {
            return response(['message' => 'Invalid Product ID !'], 401);
        }

        $product->name = $request->name;

        $product->color = $request->color;

        $product->ram = $request->ram;

        $product->storage = $request->storage;

        $product->condition = $request->condition;


        // PRODUCT IMAGE
        if(!empty($request->file('image')))
        {
            $image_name = date('YmdHis') . "_" . mt_rand(1, 999999) . "." . $request->file('image')->getClientOriginalExtension();

            $image_path = $request->file('image')->storeAs('public/product_images', $image_name);

            $product->image = asset('public' . Storage::url($image_path));
        }


        $product->save();

        return response($product, 201);
    }

    public function delete($id)
    {
        $product = Product::find($id);

        if($product == null)
        {
            return response(['message' => 'Invalid Product ID !'], 401);
        }

        $product->delete();

        return 1;
    }
}
