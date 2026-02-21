<?php
$url = "https://mediumslateblue-hummingbird-258203.hostingersite.com/api/whatsapp/webhook";
$data=["object"=>"whatsapp_business_account","entry"=>[["id"=>"1","changes"=>[["value"=>["messaging_product"=>"whatsapp","metadata"=>["display_phone_number"=>"123456","phone_number_id"=>"123456"],"contacts"=>[["profile"=>["name"=>"NAME"],"wa_id"=>"919876543210"]],"messages"=>[["from"=>"919876543210","id"=>"wamid.test.1235","timestamp"=>"1700000000","text"=>["body"=>"TESTMSG"],"type"=>"text"]]],"field"=>"messages"]]]]];
$payload = json_encode($data);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
echo "Response: " . $response . "\nCode: " . curl_getinfo($ch, CURLINFO_HTTP_CODE);

