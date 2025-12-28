<?php

namespace App\Http\Controllers\MobileControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use Carbon\Carbon;

class ChatController extends Controller
{
    protected function getAuthUser()
    {
        // support mobile JWT guards used in this project
        return auth('api')->user() ?: auth('api_admin')->user() ?: auth('web')->user() ?: auth('admin')->user();
    }

    public function contacts(Request $request)
    {
        // reuse existing web logic by delegating to app controller where possible
        $authUser = $this->getAuthUser();
        if (!$authUser) return response()->json([], 401);

        // Use the web ChatController's contacts logic by calling the existing controller
        $ctrl = app(\App\Http\Controllers\ChatController::class);
        // ensure Auth facade returns mobile user for consistency
        try { Auth::setUser($authUser); } catch (\Exception $_) {}
        return $ctrl->contacts($request);
    }

    public function messages(Request $request, $user)
    {
        $authUser = $this->getAuthUser();
        if (!$authUser) return response()->json([], 401);
        $ctrl = app(\App\Http\Controllers\ChatController::class);
        try { Auth::setUser($authUser); } catch (\Exception $_) {}
        return $ctrl->messages($request, $user);
    }

    public function search(Request $request)
    {
        $authUser = $this->getAuthUser();
        if (!$authUser) return response()->json([], 401);
        $ctrl = app(\App\Http\Controllers\ChatController::class);
        try { Auth::setUser($authUser); } catch (\Exception $_) {}
        return $ctrl->search($request);
    }

    public function send(Request $request)
    {
        $authUser = $this->getAuthUser();
        if (!$authUser) return response()->json(['error' => 'Unauthenticated'], 401);
        $ctrl = app(\App\Http\Controllers\ChatController::class);
        try { Auth::setUser($authUser); } catch (\Exception $_) {}
        return $ctrl->send($request);
    }

    public function typing(Request $request)
    {
        $authUser = $this->getAuthUser();
        if (!$authUser) return response()->json(['error' => 'Unauthenticated'], 401);
        $ctrl = app(\App\Http\Controllers\ChatController::class);
        try { Auth::setUser($authUser); } catch (\Exception $_) {}
        return $ctrl->typing($request);
    }

    public function reaction(Request $request)
    {
        $authUser = $this->getAuthUser();
        if (!$authUser) return response()->json(['error' => 'Unauthenticated'], 401);
        $ctrl = app(\App\Http\Controllers\ChatController::class);
        try { Auth::setUser($authUser); } catch (\Exception $_) {}
        return $ctrl->reaction($request);
    }
}
