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
            'project_title' => 'nullable|string|max:255',
            'theme' => 'required|in:system,light,dark',
            'primary_color' => ['required','regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
            'site_logo' => 'nullable|mimes:jpeg,jpg,png,svg|max:2048',
            'site_favicon' => 'nullable|mimes:ico,png,svg|max:256', // KB
        ]);

        // Use correct model and row
        $setting = Setting::first() ?: new Setting();

        // Handle logo upload
        if ($request->hasFile('site_logo')) {
            if ($setting->site_logo && Storage::disk('public')->exists($setting->site_logo)) {
                Storage::disk('public')->delete($setting->site_logo);
            }
            $path = $request->file('site_logo')->store('site', 'public');
            $data['site_logo'] = $path;
        }

        // Handle favicon upload
        if ($request->hasFile('site_favicon')) {
            if ($setting->site_favicon && Storage::disk('public')->exists($setting->site_favicon)) {
                Storage::disk('public')->delete($setting->site_favicon);
            }
            $fPath = $request->file('site_favicon')->store('site', 'public');
            $data['site_favicon'] = $fPath;
        }

        // Persist plain fields
        $setting->company_name = $data['company_name'] ?? $setting->company_name;
        $setting->company_address = $data['company_address'] ?? $setting->company_address;
        $setting->project_title = $data['project_title'] ?? $setting->project_title;
        $setting->theme = $data['theme'] ?? $setting->theme;
        // store both to be compatible with different usages across the app
        $setting->primary_color = $data['primary_color'] ?? $setting->primary_color;
        $setting->theme_color = $data['primary_color'] ?? $setting->theme_color;

        if (isset($data['site_logo'])) {
            $setting->site_logo = $data['site_logo'];
        }
        if (isset($data['site_favicon'])) {
            $setting->site_favicon = $data['site_favicon'];
        }

        $setting->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}