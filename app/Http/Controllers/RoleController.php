<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $request->validate([
            'name' => 'required | alpha_num | unique:roles,name',
            'description' => 'required | alpha_num'
        ], [
            'name.required' => 'Please enter the name of the role !',
            'name.alpha_num' => 'Only alphabets & numbers are allowed !',
            'name.unique' => 'Role already exists !',
            'description.required' => 'Please enter a description for this role !',
            'description.alpha_num' => 'Only alphabets & numbers are allowed !'
        ]);

        $role = new Role();

        $role->name = $request->name;

        $role->description = $request->description;

        $role->save();

        return response(['role' => $role], 201);
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
