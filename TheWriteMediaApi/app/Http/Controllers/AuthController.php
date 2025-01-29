<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){

        $fields = $request->validate([
            'user_name' => 'required|max:255',
            'user_email' => 'required|email|unique:users',
            'user_password' => 'required|confirmed',
            'user_type' => 'required|max:255'
        ]);

        $user = User::create($fields);
        $token = $user->createToken($request->user_name . ' Auth-Token')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    public function login(Request $request){
       
        $request->validate([
            'user_email' => 'required|email|exists:users',
            'user_password' => 'required'
        ]);

        $user = User::where('user_email', $request->user_email)->first();

        if(!$user ||!Hash::check($request->user_password, $user->user_password)){
            return[
                'errors' => [
                    'user_email' => ['The provided credentials are incorrect.']
                ]
               
            ];
        }
       
        $token = $user->createToken($user->user_name . ' Auth-Token')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token
        ];

    }
    public function logout(Request $request){
       $request->user()->tokens()->delete();

       return[
        'message' => 'You are logged out.'
        ];
    }
    
}
