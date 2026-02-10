<?php
/**
 * Aggressive Cache Clear - Deletes actual cache files
 */

$basePath = dirname(__DIR__);
$cachePaths = [
    $basePath . '/storage/framework/cache',
    $basePath . '/storage/framework/views',
    $basePath . '/bootstrap/cache',
];

function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }
    return true;
}

echo "Clearing caches...<br>";

foreach ($cachePaths as $path) {
    if (file_exists($path)) {
        deleteDirectory($path);
        echo "✓ Cleared: $path<br>";
    }
}

// Also try Artisan commands
if (file_exists($basePath . '/artisan')) {
    echo "<br>Running Artisan cache:clear commands...<br>";
    shell_exec('cd ' . escapeshellarg($basePath) . ' && php artisan cache:clear 2>&1');
    echo "✓ cache:clear executed<br>";
    
    shell_exec('cd ' . escapeshellarg($basePath) . ' && php artisan view:clear 2>&1');
    echo "✓ view:clear executed<br>";
    
    shell_exec('cd ' . escapeshellarg($basePath) . ' && php artisan config:clear 2>&1');
    echo "✓ config:clear executed<br>";
    
    shell_exec('cd ' . escapeshellarg($basePath) . ' && php artisan route:clear 2>&1');
    echo "✓ route:clear executed<br>";
}

echo "<br><strong>All caches cleared! Please refresh your browser with Ctrl+Shift+R</strong>";
?>
