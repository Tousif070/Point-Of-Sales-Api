<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\File;
use Storage;
use DB;
use Exception;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("brand.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $brands = Brand::join('files as f', 'f.id', '=', 'brands.file_id')
            ->select(

                'brands.id',
                'brands.name',
                'f.absolute_path as image'

            )->orderBy('brands.name', 'asc')
            ->get();

        return response(['brands' => $brands], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("brand.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'name' => 'required | string | unique:brands,name',
            'image' => 'required | image | max:2048',
        ], [
            'name.required' => 'Please enter the brand name !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'name.unique' => 'Brand name already exists !',

            'image.required' => 'Please upload an image !',
            'image.image' => 'Please upload an image file !',
            'image.max' => 'Maximum size limit is 2 MB !'
        ]);

        DB::beginTransaction();

        try {

            $brand = new Brand();

            $brand->name = $request->name;


            // BRAND IMAGE
            $image_name = date('YmdHis') . "_" . mt_rand(1, 999999) . "." . $request->file('image')->getClientOriginalExtension();

            $image_path = $request->file('image')->storeAs('public/brand_images', $image_name);

            $absolute_path = asset('public' . Storage::url($image_path));

            $file = new File();

            $file->file_path = $image_path;

            $file->absolute_path = $absolute_path;

            $file->save();


            $brand->file_id = $file->id;

            $brand->save();

            DB::commit();

            return response(['brand' => $brand], 201);

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
