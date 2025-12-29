<?php
// Simple test script to login and fetch marketing person profile
$base = 'http://127.0.0.1:8000';
$user = 'MKT001';
$pass = '12345678';

// Login
$loginUrl = $base . '/api/user/login';
$post = http_build_query(['user_code' => $user, 'password' => $pass]);
$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
        'content' => $post,
        'ignore_errors' => true,
    ]
];
$context = stream_context_create($opts);
$res = file_get_contents($loginUrl, false, $context);
if ($res === false) { echo "Login request failed\n"; exit(1); }
// Attempt JSON decode; if it fails, fallback to regex extraction (some servers stream chunks)
$login = json_decode($res, true);
if (is_array($login) && !empty($login['access_token'])) {
    $token = $login['access_token'];
} else {
    if (preg_match('/"access_token"\s*:\s*"([^"]+)"/i', $res, $m)) {
        $token = $m[1];
    } else {
        echo "Login failed response:\n" . $res . "\n";
        exit(1);
    }
}

// Fetch profile
$profileUrl = $base . '/api/marketing-person/' . $user . '/profile';
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\nAuthorization: Bearer $token\r\n",
        'ignore_errors' => true,
    ]
];
$context = stream_context_create($opts);
$res = file_get_contents($profileUrl, false, $context);
if ($res === false) { echo "Profile request failed\n"; exit(1); }
echo $res . "\n";
