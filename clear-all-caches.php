<?php
/**
 * Quick cache clearing script
 * Run: php clear-all-caches.php
 */

$commands = [
    'php artisan cache:clear',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'composer dump-autoload -q',
];

echo "Clearing all Laravel caches...\n";
echo str_repeat("=", 50) . "\n";

foreach ($commands as $cmd) {
    echo "Running: $cmd\n";
    exec($cmd, $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Success\n";
    } else {
        echo "✗ Failed\n";
        if (!empty($output)) {
            echo "Output: " . implode("\n", $output) . "\n";
        }
    }
    echo "\n";
}

echo str_repeat("=", 50) . "\n";
echo "All caches cleared!\n";
?>
