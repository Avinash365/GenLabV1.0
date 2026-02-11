import os

file_path = r'c:\Mamp\htdocs\GenLabV2.0\app\Traits\ZoomSignatureTrait.php'
content = """<?php

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
        $clientId = config('services.zoom.client_id');
        $clientSecret = config('services.zoom.client_secret');
        
        $iat = time() - 30;
        $exp = $iat + 60 * 60 * 2;

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'sdkKey' => $clientId,
            'mn' => $meetingNumber,
            'role' => $role,
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
"""

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"Written to {file_path}")
