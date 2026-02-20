<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    // Verify Webhook for Meta App configuration
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');
        
        // This token must match the one you set in your Meta App Dashboard
        // For security, you should store this in your .env or a config file
        $setting = WhatsappSetting::first();
        $myVerifyToken = optional($setting)->webhook_verify_token ?? env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'my_secure_token');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $myVerifyToken) {
                Log::info('WhatsApp Webhook Verified');
                return response($challenge, 200);
            } else {
                Log::warning('WhatsApp Webhook Verification Failed', [
                    'mode' => $mode,
                    'token' => $token
                ]);
                return response('Forbidden', 403);
            }
        }
        return response('Bad Request', 400);
    }

    // Handle Incoming Webhook Events
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('WhatsApp Webhook Received', ['payload' => $payload]);

        try {
            if (isset($payload['entry'][0]['changes'][0]['value']['messages'])) {
                $messages = $payload['entry'][0]['changes'][0]['value']['messages'];
                $contact = $payload['entry'][0]['changes'][0]['value']['contacts'][0] ?? null;
                $metadata = $payload['entry'][0]['changes'][0]['value']['metadata'] ?? null;
                
                $businessPhone = $metadata['display_phone_number'] ?? ($metadata['phone_number_id'] ?? 'unknown');

                foreach ($messages as $msg) {
                    $messageData = [
                        'meta_message_id' => $msg['id'],
                        'phone_number' => $msg['from'],
                        'receiver_number' => $businessPhone,
                        'type' => $msg['type'],
                        'meta_timestamp' => isset($msg['timestamp']) ? date('Y-m-d H:i:s', $msg['timestamp']) : now(),
                        'raw_data' => json_encode($msg),
                        'status' => 'received'
                    ];

                    if ($msg['type'] === 'text') {
                        $messageData['message'] = $msg['text']['body'];
                    } elseif ($msg['type'] === 'image') {
                        $messageData['message'] = $msg['image']['caption'] ?? '[Image]';
                        $messageData['media_info'] = $msg['image'];
                    } elseif ($msg['type'] === 'document') {
                        $messageData['message'] = $msg['document']['caption'] ?? $msg['document']['filename'] ?? '[Document]';
                        $messageData['media_info'] = $msg['document'];
                    } elseif ($msg['type'] === 'location') {
                         $messageData['message'] = "Lat: {$msg['location']['latitude']}, Long: {$msg['location']['longitude']}";
                         $messageData['media_info'] = $msg['location'];
                    } else {
                         $messageData['message'] = '[' . ucfirst($msg['type']) . ']';
                         $messageData['media_info'] = $msg[$msg['type']] ?? null;
                    }

                    // Avoid saving duplicate messages if Meta sends retries
                    WhatsappMessage::firstOrCreate(
                        ['meta_message_id' => $msg['id']],
                        $messageData
                    );
                }
            }
            
            // Also handle status updates (sent, delivered, read) if needed
            // But user specifically asked for replies (incoming messages)

            return response('EVENT_RECEIVED', 200);

        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error: ' . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }
}
