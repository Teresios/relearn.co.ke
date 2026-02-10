<?php
/**
 * Temporary file to regenerate composer autoloader
 * Visit: https://www.relearn.co.ke/composer-fix.php
 * Then delete this file
 */

echo "<h1>Regenerating Composer Autoloader...</h1>";
echo "<pre>";

$output = [];
$return = 0;

// Run composer dump-autoload
exec("composer dump-autoload 2>&1", $output, $return);

echo implode("\n", $output);
echo "\n\nReturn Code: " . $return;

if ($return === 0) {
    echo "\n\n✓ SUCCESS: Composer autoloader regenerated!";
    echo "\n\nYou can now DELETE this file: composer-fix.php";
} else {
    echo "\n\n✗ FAILED: There was an error running composer dump-autoload";
}

echo "</pre>";
?>
