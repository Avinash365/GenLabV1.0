<?php

namespace App\Http\Controllers\MobileControllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User; 
use App\Models\MarketingPersonDeviceToken; 
// use  \App\Models\MarketingPersonDeviceToken 

class UserAuthController extends Controller
{
    public function login(Request $request)
    {   

        $validator = Validator::make($request->all(), [
            'user_code' => 'required|string|exists:users,user_code',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422); 
        }

        $credentials = $request->only('user_code', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    // public function logout()
    // {
    //     Auth::guard('api')->logout();
    //     return response()->json(['message' => 'successfully logged out']);
    // }
    
    public function logout(Request $request)
    {
        // Validate incoming device token
        $request->validate([
            'device_token' => 'nullable|string'
        ]);

        // $user = auth('api')->user();

        // $user->deviceTokens()
        //     ->where('device_token', $request->device_token)
        //     ->delete();

         // If device token is provided, delete directly by token
        if ($request->filled('device_token')) {
                MarketingPersonDeviceToken::where(
                'device_token',
                $request->device_token
            )->delete();
        }

        // Log::info(''. $request->device_token); 
        // Logout user session token
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }



    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    public function profile()
    {
        // return response()->json(['message' => 'hello']);
        return response()->json(Auth::guard('api')->user());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60 * 24 * 30
        ]);
    } 


    public function saveDeviceToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'device_token' => 'required|string|unique:marketing_person_device_token,device_token',
        ]);

        // Create a new row (duplicates allowed for user but token must be unique)
        $token = MarketingPersonDeviceToken::create([
            'marketing_person_id' => $request->user_id,
            'device_token' => $request->device_token,
        ]);

        return response()->json([
            'message' => 'Device token saved successfully!'
        ]);
    } 

}
