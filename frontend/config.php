<?php
// Central API Configuration
$raw_url = getenv('API_BASE_URL') ?: (isset($_SERVER['API_BASE_URL']) ? $_SERVER['API_BASE_URL'] : 'https://cusat-store-backend.onrender.com/api');

// Clean up whitespace & trailing slashes
$raw_url = trim($raw_url);
$raw_url = rtrim($raw_url, '/');

// Ensure /api is at the end of the base URL
if (!preg_match('#/api$#i', $raw_url)) {
    $raw_url .= '/api';
}

$API_BASE_URL = $raw_url;

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
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result !== false && !empty($result) && $http_code >= 200 && $http_code < 400) {
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
    $result = @file_get_contents($url, false, $ctx);
    if ($result !== false) {
        return $result;
    }
    return false;
}
