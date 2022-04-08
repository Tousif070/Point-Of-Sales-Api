<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("permission.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $permissions = Permission::all();

        return response(['permissions' => $permissions], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required | string | unique:permissions,name',
            'description' => 'required | string',
            'permission_group' => 'required | string'
        ], [
            'name.required' => 'Please enter the name of the permission !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'name.unique' => 'Permission already exists !',
            'description.required' => 'Please enter a description for this permission !',
            'description.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'permission_group.required' => 'Please select a permission group or enter a new group !',
            'permission_group.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        $permission = new Permission();

        $permission->name = $request->name;

        $permission->description = $request->description;

        $permission->permission_group = $request->permission_group;

        $permission->save();

        return response(['permission' => $permission], 201);
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
