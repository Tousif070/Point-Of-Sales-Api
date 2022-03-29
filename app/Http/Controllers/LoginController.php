<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required | string',
            'password' => 'required | string'
        ], [
            'username.required' => 'Please enter your username !',
            'username.string' => 'Only alphabets, numbers & special characters are allowed !',
            'password.required' => 'Please enter your password !',
            'password.string' => 'Only alphabets, numbers & special characters are allowed !'
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
