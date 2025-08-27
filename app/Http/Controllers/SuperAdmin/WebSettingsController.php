<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebSettingsController extends Controller
{
    // ...existing code...
    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'theme' => 'required|in:system,light,dark',
            'primary_color' => ['required','regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
            'site_logo' => 'nullable|mimes:jpeg,jpg,png,svg|max:2048',
        ]);

        $setting = Setting::first() ?: new Setting();

        if ($request->hasFile('site_logo')) {
            if ($setting->site_logo && Storage::disk('public')->exists($setting->site_logo)) {
                Storage::disk('public')->delete($setting->site_logo);
            }
            $path = $request->file('site_logo')->store('settings', 'public');
            $data['site_logo'] = $path;
        }

        $setting->fill($data);
        $setting->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}