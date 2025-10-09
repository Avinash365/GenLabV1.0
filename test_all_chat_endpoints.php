<?php

// Comprehensive Chat API Test Script - All Endpoints (User & Admin)

$baseUrl = 'http://127.0.0.1:8000/api';

// Test credentials
$userCredentials = [
    'user_code' => 'test123',
    'password' => 'password123'
];

$adminCredentials = [
    'email' => 'admin@example.com',
    'password' => 'admin123'
];

echo "=== COMPREHENSIVE CHAT API TESTING ===\n\n";

// Helper function to make API calls
function makeApiCall($url, $method = 'GET', $data = null, $headers = []) {
    $curl = curl_init();
    $defaultHeaders = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers)
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return [
        'status_code' => $httpCode,
        'response' => $response,
        'data' => json_decode($response, true)
    ];
}

// Test authentication for both user and admin
function testAuthentication($credentials, $endpoint, $type) {
    global $baseUrl;
    
    echo "=== TESTING $type AUTHENTICATION ===\n";
    
    $result = makeApiCall("$baseUrl$endpoint", 'POST', $credentials);
    
    echo "Login Status: " . $result['status_code'] . "\n";
    
    if ($result['status_code'] === 200 && isset($result['data']['access_token'])) {
        echo "✅ $type login successful!\n";
        echo "Token: " . substr($result['data']['access_token'], 0, 50) . "...\n\n";
        return $result['data']['access_token'];
    } else {
        echo "❌ $type login failed!\n";
        echo "Response: " . $result['response'] . "\n\n";
        return null;
    }
}

// Test all chat endpoints
function testChatEndpoints($token, $prefix, $type) {
    global $baseUrl;
    
    echo "=== TESTING $type CHAT ENDPOINTS ===\n";
    
    $headers = ["Authorization: Bearer $token"];
    
    $endpoints = [
        // GET endpoints
        ['GET', '/groups', 'Get Chat Groups', null, 'get_groups'],
        ['GET', '/unread-counts', 'Get Unread Counts'],
        ['GET', '/users/search?q=test', 'Search Users'],
        
        // POST endpoints that need data
        ['POST', '/groups', 'Create Chat Group', [
            'name' => 'Test Group via ' . $type . ' ' . date('H:i:s')
        ], 'create_group'],
    ];
    
    $results = [];
    
    foreach ($endpoints as $endpoint) {
        $method = $endpoint[0];
        $path = $endpoint[1];
        $description = $endpoint[2];
        $data = $endpoint[3] ?? null;
        $key = $endpoint[4] ?? $path;
        
        echo "Testing: $description... ";
        
        $result = makeApiCall("$baseUrl$prefix$path", $method, $data, $headers);
        
        if ($result['status_code'] >= 200 && $result['status_code'] < 300) {
            echo "✅ SUCCESS ({$result['status_code']})\n";
            $results[$key] = $result['data'];
        } else {
            echo "❌ FAILED ({$result['status_code']})\n";
            echo "  Response: " . substr($result['response'], 0, 200) . "...\n";
        }
    }
    
    // Test message-related endpoints if we have groups
    $groupId = null;
    
    // First, try to use the newly created group
    if (!empty($results['create_group']['data']['id'])) {
        $groupId = $results['create_group']['data']['id'];
    }
    // If no newly created group, use existing groups
    elseif (!empty($results['get_groups']['data']['data']) && count($results['get_groups']['data']['data']) > 0) {
        $groupId = $results['get_groups']['data']['data'][0]['id'];
    }
    
    if ($groupId) {
        
        echo "\nTesting message endpoints with group ID: $groupId\n";
        
        // Get messages
        echo "Testing: Get Messages... ";
        $result = makeApiCall("$baseUrl$prefix/messages?group_id=$groupId", 'GET', null, $headers);
        if ($result['status_code'] >= 200 && $result['status_code'] < 300) {
            echo "✅ SUCCESS ({$result['status_code']})\n";
        } else {
            echo "❌ FAILED ({$result['status_code']})\n";
        }
        
        // Send message
        echo "Testing: Send Message... ";
        $messageData = [
            'group_id' => $groupId,
            'type' => 'text',
            'content' => "Test message from $type API test at " . date('Y-m-d H:i:s')
        ];
        $result = makeApiCall("$baseUrl$prefix/messages", 'POST', $messageData, $headers);
        if ($result['status_code'] >= 200 && $result['status_code'] < 300) {
            echo "✅ SUCCESS ({$result['status_code']})\n";
            
            // Test message reactions if message was created
            if (isset($result['data']['data']['id'])) {
                $messageId = $result['data']['data']['id'];
                
                echo "Testing: React to Message... ";
                $reactionData = ['type' => 'like'];
                $result = makeApiCall("$baseUrl$prefix/messages/$messageId/reactions", 'POST', $reactionData, $headers);
                if ($result['status_code'] >= 200 && $result['status_code'] < 300) {
                    echo "✅ SUCCESS ({$result['status_code']})\n";
                } else {
                    echo "❌ FAILED ({$result['status_code']})\n";
                }
            }
        } else {
            echo "❌ FAILED ({$result['status_code']})\n";
        }
    }
    
    echo "\n";
    return $results;
}

// Start testing
$userToken = testAuthentication($userCredentials, '/user/login', 'USER');
$adminToken = testAuthentication($adminCredentials, '/admin/login', 'ADMIN');

$testResults = [];

// Test user endpoints
if ($userToken) {
    $testResults['user'] = testChatEndpoints($userToken, '/chat', 'USER');
} else {
    echo "⚠️ Skipping user endpoint tests due to authentication failure\n\n";
}

// Test admin endpoints  
if ($adminToken) {
    $testResults['admin'] = testChatEndpoints($adminToken, '/admin/chat', 'ADMIN');
} else {
    echo "⚠️ Skipping admin endpoint tests due to authentication failure\n\n";
}

// Summary
echo "=== TEST SUMMARY ===\n";
echo "User Authentication: " . ($userToken ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "Admin Authentication: " . ($adminToken ? "✅ SUCCESS" : "❌ FAILED") . "\n";

if ($userToken) {
    echo "User Chat API: Available for testing\n";
    echo "  - User Token: " . substr($userToken, 0, 30) . "...\n";
}

if ($adminToken) {
    echo "Admin Chat API: Available for testing\n";
    echo "  - Admin Token: " . substr($adminToken, 0, 30) . "...\n";
}

echo "\n=== POSTMAN READY TOKENS ===\n";
if ($userToken) {
    echo "User Token:\n";
    echo "Authorization: Bearer $userToken\n\n";
}
if ($adminToken) {
    echo "Admin Token:\n";
    echo "Authorization: Bearer $adminToken\n\n";
}

echo "All endpoints tested! Check results above.\n";