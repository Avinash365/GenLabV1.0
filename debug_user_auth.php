<?php

// Debug script to isolate the user authentication issue

$baseUrl = 'http://127.0.0.1:8000/api';

// User credentials
$userCredentials = [
    'user_code' => 'test123',
    'password' => 'password123'
];

echo "=== DEBUGGING USER AUTHENTICATION ISSUE ===\n\n";

// Get user token
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "$baseUrl/user/login",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($userCredentials),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $token = $data['access_token'];
    
    echo "✅ User login successful\n";
    echo "Testing individual endpoints with timeout protection...\n\n";
    
    // Test with shorter timeout
    $endpoints = [
        '/chat/users/search?q=test' => 'Search Users (should work)',
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        echo "Testing: $description... ";
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$baseUrl$endpoint",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5, // 5 second timeout
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                'Accept: application/json'
            ]
        ]);
        
        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($result === false) {
            echo "❌ TIMEOUT or ERROR\n";
        } else {
            echo "✅ SUCCESS ($code)\n";
        }
    }
    
    echo "\nTesting problematic endpoints with very short timeout...\n";
    
    $problematicEndpoints = [
        '/chat/groups' => 'Get Groups (causes timeout)',
        '/chat/unread-counts' => 'Unread Counts (causes timeout)'
    ];
    
    foreach ($problematicEndpoints as $endpoint => $description) {
        echo "Testing: $description... ";
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$baseUrl$endpoint",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2, // Very short timeout
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                'Accept: application/json'
            ]
        ]);
        
        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if (curl_errno($curl)) {
            echo "❌ TIMEOUT/ERROR: " . curl_error($curl) . "\n";
        } else {
            echo "✅ SUCCESS ($code) - " . substr($result, 0, 100) . "...\n";
        }
        curl_close($curl);
    }
    
} else {
    echo "❌ User login failed: $response\n";
}

echo "\n=== END DEBUG ===\n";