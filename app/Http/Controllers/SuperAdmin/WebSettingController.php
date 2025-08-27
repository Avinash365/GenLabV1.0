<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebSettingController extends Controller
{
    public function edit()
    {
        $setting = SiteSetting::first();
        return view('superadmin.settings.web_settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_logo' => 'nullable|image|max:2048',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
        ]);

        $setting = SiteSetting::first();
        if (!$setting) {
            $setting = new SiteSetting();
        }

        if ($request->hasFile('site_logo')) {
            if ($setting->site_logo) {
                Storage::disk('public')->delete($setting->site_logo);
            }
            $path = $request->file('site_logo')->store('site', 'public');
            $setting->site_logo = $path;
        }

        $setting->company_name = $data['company_name'] ?? $setting->company_name;
        $setting->company_address = $data['company_address'] ?? $setting->company_address;

        $setting->save();

        return redirect()->route('superadmin.websettings.edit')->with('success', 'Settings updated successfully.');
    }
}