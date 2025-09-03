<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        // Handle avatar upload to storage/app/public/avatars/{id}.{ext}
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower($file->getClientOriginalExtension());
            $allowed = ['jpg','jpeg','png','webp'];
            // Remove any previous avatar files for this user
            foreach ($allowed as $e) {
                Storage::disk('public')->delete("avatars/{$user->id}.{$e}");
            }
            if (!in_array($ext, $allowed)) {
                $ext = 'jpg';
            }
            $file->storeAs('avatars', $user->id . '.' . $ext, 'public');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('superadmin.profile')->with('success', 'Profile updated successfully.');
    }
}
