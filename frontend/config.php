<?php
// Central API Configuration
// Points directly to the live Render backend
$API_BASE_URL = getenv('API_BASE_URL') ?: (isset($_SERVER['API_BASE_URL']) ? $_SERVER['API_BASE_URL'] : 'https://cusat-store-backend.onrender.com/api');

function api_get($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result !== false && !empty($result)) {
            return $result;
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 15.0,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);
    return @file_get_contents($url, false, $ctx);
}
