<?php
// Ultra-simple cache clearer for Laravel
// This can be run via browser at: https://www.relearn.co.ke/fix-autoload.php

@mkdir('/home/relearnc/domains/relearn.co.ke/relearn/storage/logs', 0777, true);

$bootstrapCache = '/home/relearnc/domains/relearn.co.ke/relearn/bootstrap/cache';
if (is_dir($bootstrapCache)) {
    $files = glob($bootstrapCache . '/*.php');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

// Try to recreate the cache by including autoload
$autoloadPath = '/home/relearnc/domains/relearn.co.ke/relearn/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo "✓ Autoloader reloaded<br>";
}

echo "✓ Cache cleared - please refresh your site";
die;
?>
