<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;

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
            'condition' => 'required'
        ], [
            'name.required' => 'Please enter the name of the product !',
            'color.required' => 'Please enter the color of the product !',
            'ram.required' => 'Please enter the ram allocation of the product !',
            'ram.numeric' => 'The value should be numeric !',
            'storage.required' => 'Please enter the storage allocation of the product !',
            'storage.numeric' => 'The value should be numeric !',
            'condition.required' => 'Please enter the condition of the product !'
        ]);

        $product = new Product();

        $product->name = $request->name;

        $product->color = $request->color;

        $product->ram = $request->ram;

        $product->storage = $request->storage;

        $product->condition = $request->condition;

        $product->save();

        $product->sku = $product->id + 2000;

        $product->save();

        return $product;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'color' => 'required',
            'ram' => 'required | numeric',
            'storage' => 'required | numeric',
            'condition' => 'required'
        ], [
            'name.required' => 'Please enter the name of the product !',
            'color.required' => 'Please enter the color of the product !',
            'ram.required' => 'Please enter the ram allocation of the product !',
            'ram.numeric' => 'The value should be numeric !',
            'storage.required' => 'Please enter the storage allocation of the product !',
            'storage.numeric' => 'The value should be numeric !',
            'condition.required' => 'Please enter the condition of the product !'
        ]);

        $product = Product::find($id);

        if($product == null)
        {
            return ['message' => 'Invalid Product ID !'];
        }

        $product->name = $request->name;

        $product->color = $request->color;

        $product->ram = $request->ram;

        $product->storage = $request->storage;

        $product->condition = $request->condition;

        $product->save();

        return $product;
    }

    public function delete($id)
    {
        $product = Product::find($id);

        if($product == null)
        {
            return ['message' => 'Invalid Product ID !'];
        }

        $product->delete();

        return 1;
    }
}
