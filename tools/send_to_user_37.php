<?php
$base = 'http://127.0.0.1:8000/api';
function callApi($url, $method='GET', $data=null, $headers=[]){
    $ch = curl_init();
    $default = ['Accept: application/json','Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($default, $headers));
    if ($data && in_array($method, ['POST','PUT','PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code'=>$code,'body'=>$resp,'data'=>json_decode($resp,true)];
}
function callMultipart($url, $postFields, $headers=[]){
    $ch = curl_init();
    $default = ['Accept: application/json'];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($default, $headers));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code'=>$code,'body'=>$resp,'data'=>json_decode($resp,true)];
}

// Login
$creds = ['user_code'=>'MKT001','password'=>'12345678'];
$r = callApi($base.'/user/login','POST',$creds);
echo "LOGIN -> HTTP {$r['code']}\n";
if ($r['code']!==200) { echo $r['body'] . "\n"; exit(1); }
$token = $r['data']['access_token'] ?? ($r['data']['data']['access_token'] ?? null);
if (!$token) {
    if (preg_match('/"access_token"\s*:\s*"([^"]+)"/', $r['body'], $m)) $token = $m[1];
}
if (!$token){ echo "No token obtained\n"; exit(1); }
$auth = ["Authorization: Bearer $token"];

echo "TOKEN: " . substr($token,0,80) . "...\n";
// quick check: get groups to verify auth
$g = callApi($base . '/chat/groups','GET',null,$auth);
echo "GROUPS -> HTTP {$g['code']}\n";
echo substr($g['body'],0,400) . "\n";

// Use known user id
$userId = 37;

// Get/create DM group
$directUrl = "$base/chat/direct/$userId";
$d = callApi($directUrl,'GET',null,$auth);
echo "DIRECT -> HTTP {$d['code']}\n";
echo substr($d['body'],0,800) . "\n";
if ($d['code']!==200){ exit(1); }
echo "RAW direct body (var_export): ";
var_export($d['body']);
echo "\n";
echo "DECODED direct data: ";
    $decoded = json_decode($d['body'], true);
    echo "json_last_error: " . json_last_error() . " - " . json_last_error_msg() . "\n";
    if ($decoded === null) {
        // Try to sanitize non-printable/invalid UTF-8 bytes and re-decode
        $clean = preg_replace('/[^\x09\x0A\x0D\x20-\x7F]/', '', $d['body']);
        $decoded = json_decode($clean, true);
        echo "After sanitizing, json_last_error: " . json_last_error() . " - " . json_last_error_msg() . "\n";
    }
    echo "decoded via json_decode(...) -> "; var_export($decoded); echo "\n";
    $groupId = $decoded['id'] ?? ($decoded ?? null);
if (!$groupId) { echo "No group id returned\n"; exit(1); }

// Send text message
$sendUrl = "$base/chat/messages";
$msg = ['group_id'=>(int)$groupId,'type'=>'text','content'=>'Hello Prem Prakash Sir - automated message'];
$sent = callApi($sendUrl,'POST',$msg,$auth);
echo "SEND TEXT -> HTTP {$sent['code']}\n";
echo substr($sent['body'],0,1200) . "\n";

// Upload a file
$filePath = __DIR__ . '/test_upload.txt';
if (!file_exists($filePath)) $filePath = __DIR__ . '/test_image.jpg';
if (file_exists($filePath)){
    $post = ['group_id'=>(string)$groupId,'type'=>'file','content'=>'Automated upload','file'=>new CURLFile($filePath)];
    $up = callMultipart($sendUrl, $post, $auth);
    echo "UPLOAD -> HTTP {$up['code']}\n";
    echo substr($up['body'],0,1200) . "\n";
} else {
    echo "No test file to upload\n";
}

echo "Done.\n";
?>