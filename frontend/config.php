<?php
// Central API Configuration
$raw_url = getenv('API_BASE_URL') ?: (isset($_SERVER['API_BASE_URL']) ? $_SERVER['API_BASE_URL'] : 'https://cusat-store-backend.onrender.com/api');

// Clean up whitespace
$raw_url = trim($raw_url);
// Strip trailing /products or /products/ if user provided full endpoint as API_BASE_URL
$raw_url = preg_replace('#/products/?$#i', '', $raw_url);
// Strip trailing slash
$raw_url = rtrim($raw_url, '/');

// Ensure /api is at the end of the base URL
if (!preg_match('#/api$#i', $raw_url)) {
    $raw_url .= '/api';
}

$API_BASE_URL = $raw_url;

function api_get($url, $max_attempts = 2) {
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30 seconds connect timeout for Render cold start
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds total timeout for Render cold start
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
                'timeout' => 45.0,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        $result = @file_get_contents($url, false, $ctx);
        if ($result !== false && !empty($result)) {
            // Check HTTP response code if set
            if (isset($http_response_header) && is_array($http_response_header) && count($http_response_header) > 0) {
                preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
                $status = isset($match[1]) ? intval($match[1]) : 200;
                if ($status >= 200 && $status < 400) {
                    return $result;
                }
            } else {
                return $result;
            }
        }
        
        if ($attempt < $max_attempts) {
            sleep(1);
        }
    }

    return false;
}

