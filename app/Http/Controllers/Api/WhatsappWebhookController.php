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
        // Log the raw payload for debugging purposes
        Log::channel('daily')->info('Refined Webhook Data Received:', $request->all());

        $payload = $request->all();

        // Basic validation of payload structure
        if (!isset($payload['entry']) || empty($payload['entry'])) {
            return response('No Entry', 200);
        }

        try {
            foreach ($payload['entry'] as $entry) {
                // Ensure changes existed
                if (!isset($entry['changes'])) continue;

                foreach ($entry['changes'] as $change) {
                    $value = $change['value'];

                    // 1. Handle Messages (Incoming text/media)
                    if (isset($value['messages'])) {
                        foreach ($value['messages'] as $messageData) {
                            $this->processMessage($value, $messageData);
                        }
                    }

                    // 2. Handle Status Updates (sent, delivered, read)
                    if (isset($value['statuses'])) {
                        foreach ($value['statuses'] as $statusData) {
                            $this->processStatus($statusData);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error('Webhook Processing Error: ' . $e->getMessage());
            return response('Error processing webhook', 500);
        }

        // Always return 200 OK to acknowledge receipt to Meta
        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Process and save an incoming WhatsApp message.
     */
    protected function processMessage($value, $msg)
    {
        // Extract sender info
        // 'from' is the customer phone number
        $from = $msg['from'] ?? 'Unknown';
        $wamid = $msg['id'] ?? null;
        
        // 'metadata' -> 'display_phone_number' or 'phone_number_id' is the business number
        $businessNumber = $value['metadata']['display_phone_number'] ?? 
                          ($value['metadata']['phone_number_id'] ?? 'Business');

        // Check if message already exists (idempotency)
        if ($wamid && \App\Models\WhatsappMessage::where('meta_message_id', $wamid)->exists()) {
            return;
        }

        $type = $msg['type'] ?? 'unknown';
        $body = null;
        $mediaInfo = null; // Default to null

        // Determine content based on type
        switch ($type) {
            case 'text':
                $body = $msg['text']['body'] ?? '';
                break;
            case 'image':
                $body = $msg['image']['caption'] ?? '[Image]';
                $mediaInfo = json_encode($msg['image']); // Store as json string if column is text, or array if json
                break;
            case 'document':
                $body = $msg['document']['caption'] ?? $msg['document']['filename'] ?? '[Document]';
                $mediaInfo = json_encode($msg['document']);
                break;
            case 'audio':
                $body = '[Audio]';
                $mediaInfo = json_encode($msg['audio']);
                break;
            case 'video':
                $body = $msg['video']['caption'] ?? '[Video]';
                $mediaInfo = json_encode($msg['video']);
                break;
            case 'sticker':
                $body = '[Sticker]';
                $mediaInfo = json_encode($msg['sticker']);
                break;
            case 'location':
                $body = 'Location: ' . ($msg['location']['name'] ?? '') . ' ' . ($msg['location']['address'] ?? '');
                $mediaInfo = json_encode($msg['location']);
                break;
            case 'contacts':
                $body = '[Contact]';
                $mediaInfo = json_encode($msg['contacts']);
                break;
            case 'button':
                $body = $msg['button']['text'] ?? '[Button Reply]';
                break;
            case 'interactive':
                $interactive = $msg['interactive'];
                if (isset($interactive['button_reply'])) {
                   $body = $interactive['button_reply']['title'] ?? '[Interactive Button]';
                } elseif (isset($interactive['list_reply'])) {
                   $body = $interactive['list_reply']['title'] ?? '[Interactive List]';
                } else {
                   $body = '[Interactive Message]';
                }
                break;
            default:
                $body = "[$type message]";
                break;
        }
        
        // The migration defines media_info as json type, so we can pass array directly if using Eloquent casts,
        // or ensure it matches what the database expects.
        // Let's rely on the model cast: protected $casts = ['media_info' => 'array'];
        // So passing an array is correct. Redoing the media_info assignment to be array not string.
        
        if ($type === 'image') $mediaInfo = $msg['image'];
        elseif ($type === 'document') $mediaInfo = $msg['document'];
        elseif ($type === 'audio') $mediaInfo = $msg['audio'];
        elseif ($type === 'video') $mediaInfo = $msg['video'];
        elseif ($type === 'sticker') $mediaInfo = $msg['sticker'];
        elseif ($type === 'location') $mediaInfo = $msg['location'];
        elseif ($type === 'contacts') $mediaInfo = $msg['contacts'];

        // Convert timestamp 
        $timestamp = isset($msg['timestamp']) ? \Carbon\Carbon::createFromTimestamp($msg['timestamp']) : now();

        // Create the record
        try {
            \App\Models\WhatsappMessage::create([
                'meta_message_id' => $wamid,
                'phone_number' => $from,
                'receiver_number' => $businessNumber,
                'message' => $body,
                'type' => $type,
                'status' => 'received',
                'media_info' => $mediaInfo, // Eloquent will cast array to JSON
                'meta_timestamp' => $timestamp,
                'raw_data' => json_encode($msg),
            ]);
            Log::channel('daily')->info("New Message Saved from $from: " . substr($body, 0, 50));
        } catch (\Exception $e) {
            Log::channel('daily')->error("Failed to save message from $from: " . $e->getMessage());
        }
    }

    /**
     * Update status of an existing message (sent -> delivered -> read).
     */
    protected function processStatus($status)
    {
        $wamid = $status['id'] ?? null;
        $newStatus = $status['status'] ?? null; // delivered, read, sent, failed

        // Find the message by meta_message_id
        if ($wamid && $newStatus) {
            $message = \App\Models\WhatsappMessage::where('meta_message_id', $wamid)->first();

            if ($message) {
                // Only update if status is "newer" (simplified logic data-wise)
                // or simply update to latest status received
                $message->update([
                    'status' => $newStatus,
                    'updated_at' => now(), // Touch timestamp
                ]);
                Log::channel('daily')->info("Message Status Updated: $wamid -> $newStatus");
            }
        }
    }
}
