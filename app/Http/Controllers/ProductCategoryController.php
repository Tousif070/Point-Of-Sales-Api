<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("product-category.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $product_categories = ProductCategory::all();

        return response(['product_categories' => $product_categories], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("product-category.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'name' => 'required | string | unique:product_categories,name',
            'type' => 'required'
        ], [
            'name.required' => 'Please enter the category name !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed !',
            'name.unique' => 'Category name already exists !',
            'type.required' => 'Please select the category type !'
        ]);

        $product_category = new ProductCategory();

        $product_category->name = $request->name;

        $product_category->type = $request->type;

        $product_category->save();

        return response(['product_category' => $product_category], 201);
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
