<?php

// Simple PHP script to test the Chat API authentication

$baseUrl = 'http://127.0.0.1:8000/api';

// Test credentials
$credentials = [
    'user_code' => 'test123',
    'password' => 'password123'
];

echo "=== Testing Chat API Authentication ===\n\n";

// Step 1: Login to get JWT token
echo "1. Logging in...\n";
$loginData = json_encode($credentials);

$loginCurl = curl_init();
curl_setopt_array($loginCurl, [
    CURLOPT_URL => $baseUrl . '/user/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $loginData,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$loginResponse = curl_exec($loginCurl);
$loginHttpCode = curl_getinfo($loginCurl, CURLINFO_HTTP_CODE);
curl_close($loginCurl);

echo "Login Status Code: " . $loginHttpCode . "\n";
echo "Login Response: " . $loginResponse . "\n\n";

if ($loginHttpCode === 200) {
    $loginData = json_decode($loginResponse, true);
    $token = $loginData['access_token'] ?? null;
    
    if ($token) {
        echo "✅ Login successful! Token received.\n\n";
        
        // Step 2: Test Chat Groups endpoint
        echo "2. Testing Chat Groups endpoint...\n";
        
        $groupsCurl = curl_init();
        curl_setopt_array($groupsCurl, [
            CURLOPT_URL => $baseUrl . '/chat/groups',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ]
        ]);
        
        $groupsResponse = curl_exec($groupsCurl);
        $groupsHttpCode = curl_getinfo($groupsCurl, CURLINFO_HTTP_CODE);
        curl_close($groupsCurl);
        
        echo "Chat Groups Status Code: " . $groupsHttpCode . "\n";
        echo "Chat Groups Response: " . $groupsResponse . "\n\n";
        
        if ($groupsHttpCode === 200) {
            echo "✅ Chat API authentication working correctly!\n";
            echo "You can now use this token in Postman:\n";
            echo "Authorization: Bearer " . $token . "\n";
        } else {
            echo "❌ Chat API authentication failed.\n";
        }
        
    } else {
        echo "❌ No token received in login response.\n";
    }
} else {
    echo "❌ Login failed.\n";
}

echo "\n=== Test Complete ===\n";