<?php

namespace App\Http\Controllers\MobileControllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {   

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422); 
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api_admin')->attempt($credentials)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        Auth::guard('api_admin')->logout();
        return response()->json(['message' => 'Admin successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api_admin')->refresh());
    }

    public function profile()
    {
        return response()->json(Auth::guard('api_admin')->user());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api_admin')->factory()->getTTL() * 60 * 24 * 30
        ]);
    }
}
