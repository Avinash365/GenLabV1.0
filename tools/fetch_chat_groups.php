<?php
$u = 'http://127.0.0.1:8000/chat/groups';
$ctx = stream_context_create(['http'=>['header'=>'Accept: application/json']]);
echo @file_get_contents($u, false, $ctx);
