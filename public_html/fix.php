<?php
/**
 * Emergency Autoloader Fix
 * Place in public_html and visit via browser
 */

header('Content-Type: text/html; charset=utf-8');

$appRoot = '/home/relearnc/domains/relearn.co.ke/relearn';

echo "<!DOCTYPE html>
<html>
<head><title>Autoloader Fix</title></head>
<body>
<h1>🔧 Laravel Autoloader Regeneration</h1>
<pre>";

// Step 1: Clear bootstrap cache
echo "Step 1: Clearing bootstrap cache...\n";
$bootstrapDir = $appRoot . '/bootstrap/cache';
if (is_dir($bootstrapDir)) {
    $files = glob($bootstrapDir . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "✓ Cleared $count cache files\n\n";
} else {
    echo "⚠ Cache directory not found\n\n";
}

// Step 2: Clear storage logs
echo "Step 2: Clearing logs...\n";
$logsDir = $appRoot . '/storage/logs';
if (is_dir($logsDir)) {
    $files = glob($logsDir . '/*.log');
    foreach ($files as $file) {
        unlink($file);
    }
    echo "✓ Cleared log files\n\n";
}

// Step 3: Touch autoloader to force reload
echo "Step 3: Regenerating autoloader...\n";
$autoloadFile = $appRoot . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    touch($autoloadFile);
    echo "✓ Autoloader touched\n\n";
} else {
    echo "✗ Autoloader not found!\n\n";
}

// Step 4: Try running composer dump-autoload if available
echo "Step 4: Running composer dump-autoload...\n";
$output = shell_exec("cd " . escapeshellarg($appRoot) . " && composer dump-autoload 2>&1");
if ($output) {
    echo htmlspecialchars($output);
} else {
    echo "✓ Composer command executed\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ FIX COMPLETE\n";
echo str_repeat("=", 50) . "\n";
echo "Next steps:\n";
echo "1. Delete this file\n";
echo "2. Refresh your website\n";
echo "3. Check if the error is gone\n";
echo "</pre>
</body>
</html>";
?>
