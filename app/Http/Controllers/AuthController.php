<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        if ($validateUser->fails()) {
            return response()->json(
                [
                    'message' => 'Invalid field',
                    'errors' => $validateUser->errors()
                ],
                422,
            );
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Email or Password incorrect',
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        return response()->json([
            'message' => 'Login Success',
            'user' => [
                'name' => $user->name,
                'email' =>  $user->email,
                'token' => $user->createToken("API TOKEN")->plainTextToken,
            ]
            ],200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout Success',
        ],200);
    }
}
 