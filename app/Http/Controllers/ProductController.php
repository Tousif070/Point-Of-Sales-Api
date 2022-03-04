<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
