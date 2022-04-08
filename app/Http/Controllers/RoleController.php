<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use DB;
use Exception;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("role.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $roles = Role::all();

        return response(['roles' => $roles], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("role.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'name' => 'required | string | unique:roles,name',
            'description' => 'required | string'
        ], [
            'name.required' => 'Please enter the name of the role !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'name.unique' => 'Role already exists !',
            'description.required' => 'Please enter a description for this role !',
            'description.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        DB::beginTransaction();

        try {

            $role = new Role();

            $role->name = $request->name;

            $role->description = $request->description;

            $role->save();

            DB::commit();

            return response(['role' => $role], 201);

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

    public function assignPermission(Request $request)
    {
        if(!auth()->user()->hasPermission("role.assign-permission"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        DB::beginTransaction();

        try {

            $role = Role::find($request->role_id);

            if($role == null)
            {
                return response(['message' => 'Role not found !'], 404);
            }

            $role->permissions()->sync($request->permission_ids);

            DB::commit();

            return response(['message' => 'Permission Assigned !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }


}
