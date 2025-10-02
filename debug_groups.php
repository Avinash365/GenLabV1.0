<?php

// Debug script to check group creation and data structure

$baseUrl = 'http://127.0.0.1:8000/api';

function makeApiCall($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true),
        'raw' => $response
    ];
}

echo "=== DEBUGGING GROUP CREATION ===\n";

// 1. Login as user
$loginData = [
    'user_code' => 'test123',
    'password' => 'password123'
];

$loginResult = makeApiCall("$baseUrl/user/login", 'POST', $loginData, [
    'Content-Type: application/json'
]);

if ($loginResult['status'] != 200) {
    echo "❌ Login failed: " . $loginResult['raw'] . "\n";
    exit;
}

$token = $loginResult['data']['access_token'];
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
];

echo "✅ Login successful\n";

// 2. Create a group
$groupData = [
    'name' => 'Debug Test Group',
    'description' => 'Testing group creation',
    'members' => [1] // Add self
];

echo "\n=== CREATING GROUP ===\n";
$createResult = makeApiCall("$baseUrl/chat/groups", 'POST', $groupData, $headers);
echo "Status: " . $createResult['status'] . "\n";
echo "Response: " . $createResult['raw'] . "\n";

if ($createResult['status'] == 201) {
    echo "\n=== GROUP STRUCTURE ===\n";
    print_r($createResult['data']);
    
    // Check if there's an 'id' field
    if (isset($createResult['data']['id'])) {
        $groupId = $createResult['data']['id'];
        echo "\nGroup ID found: $groupId\n";
    } elseif (isset($createResult['data']['data']['id'])) {
        $groupId = $createResult['data']['data']['id'];
        echo "\nGroup ID found in nested data: $groupId\n";
    } else {
        echo "\n❌ No ID found in response\n";
        echo "Available keys: " . implode(', ', array_keys($createResult['data'])) . "\n";
    }
}

// 3. Get all groups to see structure
echo "\n=== GETTING ALL GROUPS ===\n";
$getResult = makeApiCall("$baseUrl/chat/groups", 'GET', null, $headers);
echo "Status: " . $getResult['status'] . "\n";
echo "Response: " . $getResult['raw'] . "\n";

if ($getResult['status'] == 200) {
    echo "\n=== GROUPS LIST STRUCTURE ===\n";
    print_r($getResult['data']);
}