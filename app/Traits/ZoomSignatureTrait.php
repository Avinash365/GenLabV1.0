<?php

namespace App\Traits;

trait ZoomSignatureTrait
{
    /**
     * Generate the Zoom Meeting Signature (HMAC SHA256)
     * 
     * @param string $meetingNumber The 9-11 digit meeting ID
     * @param int $role 0 for Attendee, 1 for Host
     * @return string
     */
    public function generateSignature($meetingNumber, $role = 0)
    {
        // Use SDK specific credentials if available, otherwise fallback to standard
        $clientId = config('services.zoom.sdk_client_id') ?? config('services.zoom.client_id');
        $clientSecret = config('services.zoom.sdk_client_secret') ?? config('services.zoom.client_secret');
        
        // Sanitize meeting number (digits only)
        $meetingNumber = preg_replace('/[^0-9]/', '', (string)$meetingNumber);

        // Time window: slightly larger buffer for server drift
        $iat = time() - 120; // 2 minutes past
        $exp = $iat + 60 * 60 * 2; // 2 hours

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'sdkKey' => $clientId,
            'appKey' => $clientId, // Backward compatibility
            'mn' => (int)$meetingNumber,
            'role' => (int)$role,
            'iat' => $iat,
            'exp' => $exp,
            'tokenExp' => $exp
        ];

        // JWT logic
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $clientSecret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
