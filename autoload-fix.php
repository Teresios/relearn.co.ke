<?php
/**
 * Autoloader Fix Script
 * Run this to regenerate composer autoloader
 * Then delete this file
 */

$appPath = '/home/relearnc/domains/relearn.co.ke/relearn';

echo "<h2>Fixing Composer Autoloader...</h2>";
echo "<pre>";

// Change to app directory
chdir($appPath);

// Try to run composer dump-autoload
$output = shell_exec("composer dump-autoload 2>&1");
echo htmlspecialchars($output);

echo "\n\n";

// Also clear Laravel cache
if (file_exists($appPath . '/bootstrap/cache')) {
    $files = glob($appPath . '/bootstrap/cache/*');
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitkeep') {
            unlink($file);
            echo "✓ Cleared: " . basename($file) . "\n";
        }
    }
}

echo "\n✓ Fix complete! Delete this file and refresh your site.";
echo "</pre>";
?>
