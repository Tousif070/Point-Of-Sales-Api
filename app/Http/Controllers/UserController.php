<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return response(['users' => $users], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required | alpha',
            'last_name' => 'required | alpha',
            'username' => 'required | alpha_num | unique:users,username',
            'email' => 'required | email | unique:users,email',
            'password' => 'required | alpha_num | confirmed',
            'pin_number' => 'required | numeric',
            'type' => 'required | numeric'
        ], [
            'first_name.required' => 'Please enter your first name !',
            'first_name.alpha' => 'Only alphabets are allowed !',

            'last_name.required' => 'Please enter your last name !',
            'last_name.alpha' => 'Only alphabets are allowed !',

            'username.required' => 'Please enter your username !',
            'username.alpha_num' => 'Only alphabets & numbers are allowed !',
            'username.unique' => 'Username already exists !',

            'email.required' => 'Please enter your email !',
            'email.email' => 'Please enter a valid email !',
            'email.unique' => 'Email already exists !',

            'password.required' => 'Please enter a password !',
            'password.alpha_num' => 'Only alphabets & numbers are allowed !',
            'password.confirmed' => 'Passwords do not match !',

            'pin_number.required' => 'Please enter a pin number !',
            'pin_number.numeric' => 'Only numbers are allowed !',

            'type.required' => 'Please select the type of user !',
            'type.numeric' => 'Type should be numeric !'
        ]);

        $user = new User();

        $user->first_name = $request->first_name;

        $user->last_name = $request->last_name;

        $user->username = $request->username;

        $user->email = $request->email;

        $user->password = Hash::make($request->password);

        $user->pin_number = $request->pin_number;

        $user->type = $request->type;

        $user->save();

        return response(['user' => $user], 201);
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
