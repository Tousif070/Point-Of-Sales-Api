<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function register(Request $request)
    {
        $user = new User();

        $user->name = $request->name;

        $user->email = $request->email;

        $user->password = Hash::make($request->password);

        $user->save();

        return $user;
    }

    public function login(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();

        if($user == null || !Hash::check($request->password, $user->password))
        {
            return response(['message' => 'Invalid Email/Password !'], 401);
        }

        $user_token = $user->createToken(mt_rand(1, 1000000) . "_" . $user->email);

        return response(['user_token' => $user_token->plainTextToken], 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return ['message' => 'Logged Out !'];
    }
}
