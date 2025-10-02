<?php

// Debug script for message endpoints

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

echo "=== DEBUGGING MESSAGE ENDPOINTS ===\n";

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

// 2. Get existing groups
echo "\n=== GETTING GROUPS ===\n";
$groupsResult = makeApiCall("$baseUrl/chat/groups", 'GET', null, $headers);

if ($groupsResult['status'] != 200 || empty($groupsResult['data']['data'])) {
    echo "❌ No groups available\n";
    exit;
}

$groupId = $groupsResult['data']['data'][0]['id'];
echo "Using group ID: $groupId\n";

// 3. Test getting messages
echo "\n=== TESTING GET MESSAGES ===\n";
$messagesUrl = "$baseUrl/chat/messages?group_id=$groupId";
echo "URL: $messagesUrl\n";
$messagesResult = makeApiCall($messagesUrl, 'GET', null, $headers);
echo "Status: " . $messagesResult['status'] . "\n";
echo "Response: " . $messagesResult['raw'] . "\n";

// 4. Test sending message
echo "\n=== TESTING SEND MESSAGE ===\n";
$messageData = [
    'group_id' => $groupId,
    'type' => 'text',
    'content' => 'Debug test message'
];

$sendResult = makeApiCall("$baseUrl/chat/messages", 'POST', $messageData, $headers);
echo "Status: " . $sendResult['status'] . "\n";
echo "Response: " . $sendResult['raw'] . "\n";