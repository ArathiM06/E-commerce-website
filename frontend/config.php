<?php
// Central API Configuration
// Points directly to the live Render backend
$API_BASE_URL = getenv('API_BASE_URL') ?: (isset($_SERVER['API_BASE_URL']) ? $_SERVER['API_BASE_URL'] : 'https://cusat-store-backend.onrender.com/api');
