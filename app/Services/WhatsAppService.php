<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $phoneNumberId;
    protected $apiVersion;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = 'v18.0';
    }

    /**
     * Send a template message via WhatsApp Cloud API.
     *
     * @param string $to Recipient phone number in international format (e.g., 919876543210)
     * @param string $templateName The template name created in Meta Business Manager
     * @param array $components The components (parameters) for the template
     * @param string $language The language code (default: en_US)
     * @return bool
     */
    public function sendTemplateMessage($to, $templateName, $components, $language = 'en_US')
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::error('WhatsApp credentials are missing in config/services.php');
            return false;
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        // Basic payload structure
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $components
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withToken($this->token)
                ->contentType('application/json')
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to {$to}", $response->json());
                return true;
            } else {
                Log::error("WhatsApp API Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Exception: " . $e->getMessage());
            return false;
        }
    }
}
