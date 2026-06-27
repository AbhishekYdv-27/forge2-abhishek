<?php
if (php_sapi_name() == 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $url;
    
    if (is_file($file) && preg_match('/\.(js|css|gif|png|jpg|jpeg|txt|ico|svg|webmanifest)$/i', $file)) {
        return false;
    }
}
require_once __DIR__ . '/index.php';
