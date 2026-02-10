<?php
$appPath = __DIR__ . '/../relearn';

try {
    require $appPath . '/vendor/autoload.php';
    $app = require $appPath . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<h1>Clearing All Caches</h1>";
    echo "<hr>";
    
    // Clear view cache
    $exitCode = $kernel->call('view:clear');
    echo "✓ Cleared view cache<br>";
    
    // Clear config cache
    $exitCode = $kernel->call('config:clear');
    echo "✓ Cleared config cache<br>";
    
    // Clear application cache
    $exitCode = $kernel->call('cache:clear');
    echo "✓ Cleared application cache<br>";
    
    // Clear route cache
    $exitCode = $kernel->call('route:clear');
    echo "✓ Cleared route cache<br>";
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ All caches cleared successfully!</h2>";
    echo "<p>You can now delete this file for security.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
