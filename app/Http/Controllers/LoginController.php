<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required | alpha',
            'email' => 'required | email | unique:users,email',
            'password' => 'required | alpha_num | confirmed'
        ], [
            'name.required' => 'Please enter your name !',
            'name.alpha' => 'Only alphabets are allowed !',
            'email.required' => 'Please enter your email !',
            'email.email' => 'Please enter a valid email !',
            'email.unique' => 'Email already exists !',
            'password.required' => 'Please enter a password !',
            'password.alpha_num' => 'Only alphabets & numbers are allowed !',
            'password.confirmed' => 'Passwords do not match !'
        ]);

        $user = new User();

        $user->name = $request->name;

        $user->email = $request->email;

        $user->password = Hash::make($request->password);

        $user->save();

        return $user;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required | alpha_num',
            'password' => 'required | alpha_num'
        ], [
            'username.required' => 'Please enter your username !',
            'username.alpha_num' => 'Only alphabets & numbers are allowed !',
            'password.required' => 'Please enter your password !',
            'password.alpha_num' => 'Only alphabets & numbers are allowed !'
        ]);

        $user = User::where('username', '=', $request->username)->first();

        if($user == null || !Hash::check($request->password, $user->password))
        {
            return response(['message' => 'Invalid Username or Password !'], 404);
        }

        $user_token = $user->createToken(mt_rand(1, 1000000) . "_" . $user->email);

        return response(['user_token' => $user_token->plainTextToken], 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response(['message' => 'Logged Out !'], 200);
    }
}
