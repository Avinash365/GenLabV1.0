<?php

// Comprehensive Chat API Test Script - All Endpoints (User & Admin)

$baseUrl = 'http://127.0.0.1:8000/api';

// Test credentials
$userCredentials = [
    'user_code' => 'MKT001',
    'password' => '12345678'
];

$adminCredentials = [
    'email' => 'superadmin1@example.com',
    'password' => 'password123'
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

// Make multipart/form-data API call (for file uploads)
function makeMultipartApiCall($url, $postFields = [], $headers = []) {
    $curl = curl_init();
    $defaultHeaders = [
        'Accept: application/json'
    ];
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return [
        'status_code' => $httpCode,
        'response' => $response,
        'data' => json_decode($response, true)
    ];
}

// Test file upload and fetching (tries common field names)
function testFileUploadAndFetch($token, $prefix, $type) {
    global $baseUrl;
    echo "=== TESTING $type FILE UPLOAD/DOWNLOAD ===\n";

    $headers = ["Authorization: Bearer $token"];

    $filesToTry = [
        ['field'=>'file','path'=>__DIR__ . '/tools/test_upload.txt'],
        ['field'=>'file','path'=>__DIR__ . '/tools/test_image.jpg'],
        ['field'=>'attachments[]','path'=>__DIR__ . '/tools/test_image.jpg']
    ];

    foreach ($filesToTry as $f) {
        if (!file_exists($f['path'])) continue;
        echo "Uploading ({$f['field']}) -> {$f['path']}... ";
        $post = [
            'group_id' => 1,
            'type' => 'file',
            'content' => 'file upload test',
        ];
        $post[$f['field']] = new CURLFile($f['path']);
        $res = makeMultipartApiCall("$baseUrl$prefix/messages", $post, $headers);
        if ($res['status_code'] >= 200 && $res['status_code'] < 300) {
            echo "✅ SUCCESS ({$res['status_code']})\n";
            // try to find a downloadable URL in response
            $body = $res['response'];
            if (preg_match('/https?:\\/\\/[^\s\"\']+/', $body, $m)) {
                $url = $m[0];
                echo "Found URL: $url - attempting download... ";
                $dl = makeApiCall($url, 'GET');
                if ($dl['status_code'] === 200 && strlen($dl['response'])>0) {
                    $out = __DIR__ . '/tools/downloaded_' . basename($f['path']);
                    file_put_contents($out, $dl['response']);
                    echo "✅ Downloaded to $out (" . filesize($out) . " bytes)\n";
                } else {
                    echo "❌ Failed to download ({$dl['status_code']})\n";
                }
            } else {
                echo "No URL found in response to download. Response excerpt: " . substr($body,0,200) . "...\n";
            }
        } else {
            echo "❌ FAILED ({$res['status_code']})\n";
            echo "  Response: " . substr($res['response'],0,300) . "...\n";
        }
    }
    echo "\n";
}

// Test authentication for both user and admin
function testAuthentication($credentials, $endpoint, $type) {
    global $baseUrl;
    
    echo "=== TESTING $type AUTHENTICATION ===\n";
    
    $result = makeApiCall("$baseUrl$endpoint", 'POST', $credentials);
    
    echo "Login Status: " . $result['status_code'] . "\n";
    $token = null;
    if (isset($result['data']['access_token'])) {
        $token = $result['data']['access_token'];
    } elseif (isset($result['data']['data']['access_token'])) {
        $token = $result['data']['data']['access_token'];
    } else {
        $decoded = json_decode($result['response'], true);
        if (is_array($decoded)) {
            if (isset($decoded['access_token'])) {
                $token = $decoded['access_token'];
            } elseif (isset($decoded['data']['access_token'])) {
                $token = $decoded['data']['access_token'];
            }
        }
    }

    // Final fallback: extract access_token via regex from raw response
    if (!$token && preg_match('/"access_token"\s*:\s*"([^"]+)"/i', $result['response'], $matches)) {
        $token = $matches[1];
    }

    if ($result['status_code'] === 200 && $token) {
        echo "✅ $type login successful!\n";
        echo "Token: " . substr($token, 0, 50) . "...\n\n";
        return $token;
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
                
                // Get single message
                echo "Testing: Get Single Message... ";
                $r2 = makeApiCall("$baseUrl$prefix/messages/$messageId", 'GET', null, $headers);
                echo ($r2['status_code'] >= 200 && $r2['status_code'] < 300) ? "✅ SUCCESS ({$r2['status_code']})\n" : "❌ FAILED ({$r2['status_code']})\n";
                
                echo "Testing: React to Message... ";
                $reactionData = ['type' => 'like'];
                $result = makeApiCall("$baseUrl$prefix/messages/$messageId/reactions", 'POST', $reactionData, $headers);
                if ($result['status_code'] >= 200 && $result['status_code'] < 300) {
                    echo "✅ SUCCESS ({$result['status_code']})\n";
                } else {
                    echo "❌ FAILED ({$result['status_code']})\n";
                }

                // Reply to the message
                echo "Testing: Reply to Message... ";
                $replyData = ['type' => 'text', 'content' => "Reply from $type at " . date('H:i:s')];
                $r3 = makeApiCall("$baseUrl$prefix/messages/$messageId/reply", 'POST', $replyData, $headers);
                if ($r3['status_code'] >= 200 && $r3['status_code'] < 300) {
                    echo "✅ SUCCESS ({$r3['status_code']})\n";
                } else {
                    echo "❌ FAILED ({$r3['status_code']})\n";
                }

                // Create another group to forward/share
                echo "Testing: Create 2nd Group (for forward/share)... ";
                $g2 = makeApiCall("$baseUrl$prefix/groups", 'POST', ['name' => 'Fwd Group via ' . $type . ' ' . date('His')], $headers);
                if ($g2['status_code'] >= 200 && $g2['status_code'] < 300 && isset($g2['data']['data']['id'])) {
                    $gid2 = $g2['data']['data']['id'];
                    echo "✅ SUCCESS ({$g2['status_code']})\n";

                    // Forward
                    echo "Testing: Forward Message... ";
                    $r4 = makeApiCall("$baseUrl$prefix/messages/$messageId/forward", 'POST', ['target_group_ids' => [$gid2]], $headers);
                    echo ($r4['status_code'] >= 200 && $r4['status_code'] < 300) ? "✅ SUCCESS ({$r4['status_code']})\n" : "❌ FAILED ({$r4['status_code']})\n";

                    // Share
                    echo "Testing: Share Message... ";
                    $r5 = makeApiCall("$baseUrl$prefix/messages/$messageId/share", 'POST', ['target_group_id' => $gid2], $headers);
                    echo ($r5['status_code'] >= 200 && $r5['status_code'] < 300) ? "✅ SUCCESS ({$r5['status_code']})\n" : "❌ FAILED ({$r5['status_code']})\n";
                } else {
                    echo "❌ FAILED creating 2nd group\n";
                }

                // Set status (cycle through values)
                foreach (["hold","booked","cancel"] as $st) {
                    echo "Testing: Set Status ($st)... ";
                    $r6 = makeApiCall("$baseUrl$prefix/messages/$messageId/status", 'POST', ['status' => $st], $headers);
                    echo ($r6['status_code'] >= 200 && $r6['status_code'] < 300) ? "✅ SUCCESS ({$r6['status_code']})\n" : "❌ FAILED ({$r6['status_code']})\n";
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
    // run file upload/download tests for user
    testFileUploadAndFetch($userToken, '/chat', 'USER');
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