<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{SiteSetting,SpecialFeature};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebSettingController extends Controller
{
    public function edit()
    {
        $setting = SiteSetting::first();

        $feature = SpecialFeature::first() ?? new SpecialFeature(['backed_booking' => 0]);

        return view('superadmin.settings.web_settings', compact('setting', 'feature'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_logo' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|mimes:ico,png,jpg,jpeg,svg|max:256',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'project_title' => 'nullable|string|max:255',
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

        if ($request->hasFile('site_favicon')) {
            if ($setting->site_favicon) {
                Storage::disk('public')->delete($setting->site_favicon);
            }
            $faviconPath = $request->file('site_favicon')->store('site', 'public');
            $setting->site_favicon = $faviconPath;
        }

        $setting->company_name = $data['company_name'] ?? $setting->company_name;
        $setting->company_address = $data['company_address'] ?? $setting->company_address;
        $setting->project_title = $data['project_title'] ?? $setting->project_title;

        $setting->save();

        return redirect()->route('superadmin.websettings.edit')->with('success', 'Settings updated successfully.');
    } 

    public function updateBackedBooking(Request $request)
    {


        $feature = SpecialFeature::first();
        if (!$feature) {
            $feature = new SpecialFeature();
        }

        // Update backed_booking value (assume checkbox or toggle sends 1 or 0)
        $feature->backed_booking = $request->input('backed_booking', 0);
        $feature->save();

        // Create message based on status
        $status = $feature->backed_booking ? 'ON' : 'OFF';
        $message = "Backdated booking feature is now {$status}.";

        return redirect()->back()->with('success', $message);
    }

}