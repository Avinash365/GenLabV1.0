<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappSetting;
use Illuminate\Http\Request;

class WhatsappSettingController extends Controller
{
    public function __construct() {
        // You can add permission middleware here if needed
        // $this->middleware('permission:web_setting.edit');
    }

    public function index()
    {
        $setting = WhatsappSetting::first();
        return view('superadmin.settings.whatsapp_settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'phone_number_id' => 'nullable|string|max:255',
            'business_account_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
            'app_id' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'default_template_name' => 'nullable|string|max:255',
            'hold_template_name' => 'nullable|string|max:255',
            'report_template_name' => 'nullable|string|max:255',
            'dispatch_template_name' => 'nullable|string|max:255',
            'default_language' => 'nullable|string|max:10',
            'webhook_verify_token' => 'nullable|string|max:255',
        ]);

        $setting = WhatsappSetting::first();
        if (!$setting) {
            WhatsappSetting::create($data);
        } else {
            $setting->update($data);
        }

        return redirect()->back()->with('success', 'WhatsApp settings updated successfully.');
    }
}
