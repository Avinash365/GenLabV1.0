<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        return view('superadmin.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                Rule::unique($user->getTable(),'email')->ignore($user->getKey(), $user->getKeyName())
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('superadmin.profile')->with('success', 'Profile updated successfully.');
    }
}
