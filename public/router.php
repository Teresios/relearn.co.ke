<?php

if (php_sapi_name() === 'cli-server') {
    $_SERVER['PHP_SELF'] = '/index.php';
    
    $url  = parse_url($_SERVER["REQUEST_URI"]);
    $file = __DIR__ . $url["path"];
    
    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/index.php';
