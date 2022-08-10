<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use REC;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required | string',
            'password' => 'required | string'
        ], [
            'username.required' => 'Please enter your username !',
            'username.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'password.required' => 'Please enter your password !',
            'password.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        $user = User::where('username', '=', $request->username)->first();

        if($user == null || !Hash::check($request->password, $user->password))
        {
            return response(['message' => 'Invalid Username or Password !'], 404);
        }

        $user_token = $user->createToken(mt_rand(1, 1000000) . "_" . $user->email);


        // RECORD ENTRY FOR USER LOGIN
        $rec_data_arr = [
            'user_id' => $user->id,
            'type' => "Login",
            'user_type' => $user->type
        ];

        REC::storeLoginLogoutRecord($rec_data_arr);


        return response([
            'user' => $user,
            'user_token' => $user_token->plainTextToken
        ], 200);
    }

    public function logout()
    {
        $user = auth()->user();


        // RECORD ENTRY FOR USER LOGOUT
        $rec_data_arr = [
            'user_id' => $user->id,
            'type' => "Logout",
            'user_type' => $user->type
        ];

        REC::storeLoginLogoutRecord($rec_data_arr);


        $user->tokens()->delete();

        return response(['message' => 'Logged Out !'], 200);
    }
}
