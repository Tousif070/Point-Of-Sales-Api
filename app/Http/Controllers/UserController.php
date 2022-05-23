<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use DB;
use Exception;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexOfficial()
    {
        if(!auth()->user()->hasPermission("user.index-official"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $users = User::with(['roles'])->where('type', '=', 1)->get();

        return response(['users' => $users], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexCustomer()
    {
        if(!auth()->user()->hasPermission("user.index-customer"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $users = User::where('type', '=', 2)->get();

        return response(['users' => $users], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexSupplier()
    {
        if(!auth()->user()->hasPermission("user.index-supplier"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $users = User::where('type', '=', 3)->get();

        return response(['users' => $users], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerOfficial(Request $request)
    {
        if(!auth()->user()->hasPermission("user.register-official"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'first_name' => 'required | string',
            'last_name' => 'required | string',
            'username' => 'required | string | unique:users,username',
            'email' => 'required | email | unique:users,email',
            'password' => 'required | string | confirmed | min:8',
            'pin_number' => 'required | numeric | min:10000'
        ], [
            'first_name.required' => 'Please enter your first name !',
            'first_name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'last_name.required' => 'Please enter your last name !',
            'last_name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'username.required' => 'Please enter your username !',
            'username.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'username.unique' => 'Username already exists !',

            'email.required' => 'Please enter your email !',
            'email.email' => 'Please enter a valid email !',
            'email.unique' => 'Email already exists !',

            'password.required' => 'Please enter a password !',
            'password.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'password.confirmed' => 'Passwords do not match !',
            'password.min' => 'Password should be minimum of 8 characters !',

            'pin_number.required' => 'Please enter a pin number !',
            'pin_number.numeric' => 'Only numbers are allowed !',
            'pin_number.min' => 'Pin number should be minimum of 5 digits !'
        ]);

        DB::beginTransaction();

        try {

            $user = new User();

            $user->first_name = $request->first_name;

            $user->last_name = $request->last_name;

            $user->username = $request->username;

            $user->email = $request->email;

            $user->password = Hash::make($request->password);

            $user->pin_number = $request->pin_number;

            $user->type = 1;

            $user->save();


            $user_details = new UserDetail();

            $user_details->user_id = $user->id;

            $user_details->contact_no = "";

            $user_details->address = "";

            $user_details->city = "";

            $user_details->state = "";

            $user_details->country = "";

            $user_details->zip_code = "";

            $user_details->save();


            DB::commit();

            return response(['user' => $user], 201);

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
    public function registerCustomer(Request $request)
    {
        if(!auth()->user()->hasPermission("user.register-customer"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'first_name' => 'required | string',
            'last_name' => 'required | string',
            'username' => 'required | string | unique:users,username',
            'email' => 'required | email | unique:users,email'
        ], [
            'first_name.required' => 'Please enter your first name !',
            'first_name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'last_name.required' => 'Please enter your last name !',
            'last_name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'username.required' => 'Please enter your username !',
            'username.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'username.unique' => 'Username already exists !',

            'email.required' => 'Please enter your email !',
            'email.email' => 'Please enter a valid email !',
            'email.unique' => 'Email already exists !'
        ]);

        DB::beginTransaction();

        try {

            $user = new User();

            $user->first_name = $request->first_name;

            $user->last_name = $request->last_name;

            $user->username = $request->username;

            $user->email = $request->email;

            $user->password = Hash::make("11111111");

            $user->pin_number = 12345;

            $user->type = 2;

            $user->save();

            DB::commit();

            return response(['user' => $user], 201);

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
    public function registerSupplier(Request $request)
    {
        if(!auth()->user()->hasPermission("user.register-supplier"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'first_name' => 'required | string',
            'last_name' => 'required | string',
            'username' => 'required | string | unique:users,username',
            'email' => 'required | email | unique:users,email'
        ], [
            'first_name.required' => 'Please enter your first name !',
            'first_name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'last_name.required' => 'Please enter your last name !',
            'last_name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'username.required' => 'Please enter your username !',
            'username.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'username.unique' => 'Username already exists !',

            'email.required' => 'Please enter your email !',
            'email.email' => 'Please enter a valid email !',
            'email.unique' => 'Email already exists !'
        ]);

        DB::beginTransaction();

        try {

            $user = new User();

            $user->first_name = $request->first_name;

            $user->last_name = $request->last_name;

            $user->username = $request->username;

            $user->email = $request->email;

            $user->password = Hash::make("11111111");

            $user->pin_number = 12345;

            $user->type = 3;

            $user->save();

            DB::commit();

            return response(['user' => $user], 201);

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
    public function showOfficial($user_id)
    {
        if(!auth()->user()->hasPermission("user.index-official"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $user = User::join('user_details as ud', 'ud.user_id', '=', 'users.id')
            ->select(

                'users.id',
                'users.first_name',
                'users.last_name',
                'users.username',
                'users.email',
                'ud.contact_no',
                'ud.address',
                'ud.city',
                'ud.state',
                'ud.country',
                'ud.zip_code'

            )->where('users.id', '=', $user_id)
            ->first();
        
        if($user == null)
        {
            return response(['message' => 'User not found !'], 404);
        }
        
        return response(['user' => $user], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showCustomer($id)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showSupplier($id)
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

    public function assignRoleView($user_id)
    {
        if(!auth()->user()->hasPermission("user.assign-role"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $user = User::find($user_id);

        if($user == null)
        {
            return response(['message' => 'User not found !'], 404);
        }

        $roles = Role::select(['id', 'name', 'description'])->get();

        return response([
            'user_name' => $user->first_name . " " . $user->last_name,
            'user_roles' => $user->roles,
            'all_roles' => $roles
        ], 200);
    }

    public function assignRole(Request $request)
    {
        if(!auth()->user()->hasPermission("user.assign-role"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        DB::beginTransaction();

        try {

            $user = User::find($request->user_id);

            if($user == null)
            {
                return response(['message' => 'User not found !'], 404);
            }

            $user->roles()->sync($request->role_ids);

            DB::commit();

            return response([
                'message' => 'Role Assigned !',
                'user_roles' => $user->roles
            ], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function hasPermission(Request $request)
    {
        return response([
            'value' => auth()->user()->hasPermission($request->permission),
            'permission_name' => $request->permission
        ], 200);
    }

    public function getRoles($user_id)
    {
        return response(['roles' => auth()->user()->getRoles()], 200);
    }

    public function getPermissions()
    {
        return response(['permissions' => auth()->user()->getPermissions()], 200);
    }


}
