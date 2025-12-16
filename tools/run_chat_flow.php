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

// 1) Login
$creds = ['user_code'=>'MKT001','password'=>'12345678'];
$r = callApi($base.'/user/login','POST',$creds);
echo "LOGIN (POST $base/user/login) -> HTTP {$r['code']}\n";
echo substr($r['body'],0,800) . "\n\n";
if ($r['code']!==200){ exit(0); }
$token = null;
if (!empty($r['data']['access_token'])) $token = $r['data']['access_token'];
elseif (!empty($r['data']['data']['access_token'])) $token = $r['data']['data']['access_token'];
else {
    $m = [];
    if (preg_match('/"access_token"\s*:\s*"([^"]+)"/',$r['body'],$m)) $token = $m[1];
}
if (!$token){ echo "No token obtained\n"; exit(0); }
$auth = ["Authorization: Bearer $token"];

// 2) Search users for 'Prem Prakash Sir'
$q = urlencode('Prem Prakash Sir');
$searchUrl = "$base/chat/users/search?q=$q";
$s = callApi($searchUrl,'GET',null,$auth);
echo "SEARCH (GET $searchUrl) -> HTTP {$s['code']}\n";
echo substr($s['body'],0,1000) . "\n\n";

// pick first user id
$userId = null;
if (!empty($s['data']) && is_array($s['data'])){
    $first = $s['data'][0] ?? null;
    if ($first && !empty($first['id'])) $userId = $first['id'];
}
if (!$userId){ echo "No users found to DM.\n"; exit(0); }

// 3) Get/create DM group
$directUrl = "$base/chat/direct/$userId";
$d = callApi($directUrl,'GET',null,$auth);
echo "DIRECT (GET $directUrl) -> HTTP {$d['code']}\n";
echo substr($d['body'],0,800) . "\n\n";
$groupId = null;
if (!empty($d['data']['id'])) $groupId = $d['data']['id'];
elseif (!empty($d['data'])) $groupId = $d['data'];
if (!$groupId){ echo "Could not determine group_id from direct response\n"; exit(0); }

// 4) Send text message
$msg = ['group_id'=>(int)$groupId,'type'=>'text','content'=>'Hello from automated test'];
$sendUrl = "$base/chat/messages";
$sent = callApi($sendUrl,'POST',$msg,$auth);
echo "SEND TEXT (POST $sendUrl) -> HTTP {$sent['code']}\n";
echo substr($sent['body'],0,1200) . "\n\n";

// 5) Upload file (multipart) using 'file' field
$filePath = __DIR__ . '/test_upload.txt';
if (!file_exists($filePath)) $filePath = __DIR__ . '/test_image.jpg';
if (file_exists($filePath)){
    $post = ['group_id'=>(string)$groupId,'type'=>'file','content'=>'Upload from script','file'=>new CURLFile($filePath)];
    $up = callMultipart($sendUrl, $post, $auth);
    echo "UPLOAD (POST $sendUrl multipart) -> HTTP {$up['code']}\n";
    echo substr($up['body'],0,1200) . "\n\n";
} else {
    echo "No test file available to upload at $filePath\n";
}

echo "Done. Exact endpoints used printed above.\n";
?>