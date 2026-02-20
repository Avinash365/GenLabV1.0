<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappSetting;

class WhatsappWebhookController extends Controller
{
    /**
     * Verify the webhook with Meta (GET request)
     */
    public function verify(Request $request)
    {
        // 1. Log full input for debugging
        $params = $request->query();
        Log::channel('daily')->info('Webhook Verify Hit', $params);

        // 2. Extract paraameters
        $mode = $request->query('hub_mode');
        $verifyToken = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        // 3. Determine expected token
        // Use DB setting if available, otherwise env default
        $internalToken = null;
        try {
            $setting = WhatsappSetting::first();
            $internalToken = optional($setting)->webhook_verify_token;
        } catch (\Exception $e) {
            Log::channel('daily')->error('Webhook DB Error: ' . $e->getMessage());
        }
        
        if (empty($internalToken)) {
            // Default token as fallback
            $internalToken = env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'GenSkytech_Secret_2027');
        }

        // Trim whitespace just in case of copy-paste errors
        $internalToken = trim((string)$internalToken);

        Log::channel('daily')->info("Comparing: Received '$verifyToken' vs Internal '$internalToken'");

        // 4. Validate
        if ($mode === 'subscribe' && $verifyToken === $internalToken) {
            Log::channel('daily')->info('Webhook Verified Successfully! Returning challenge: ' . $challenge);
            
            // Return challenge string directly with correct content type
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        // 5. Fail
        Log::channel('daily')->warning('Webhook Verification Failed!');
        return response('Forbidden', 403); 
    }

    /**
     * Handle incoming webhook events (POST request)
     */
    public function handle(Request $request)
    {
        // Just return 200 OK immediately for now
        return response('EVENT_RECEIVED', 200);
    }
}
