<?php
$pub = file_get_contents(base_path('keys/tym-pub.pem'));
$pri = file_get_contents(base_path('keys/tym.pem'));
return [
    'userms' => env('USER_MS', "userms.vb.test"),
    'public_key' => env('AUTH_PUB_KEY')??$pub,
    'private_key' => env('AUTH_PRIVATE_KEY')??$pri,
];
