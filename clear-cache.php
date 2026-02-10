<?php
try {
    $appPath = __DIR__ . '/../relearn';
    
    require $appPath . '/vendor/autoload.php';
    
    $app = require $appPath . '/bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<h2>Clearing Laravel Caches</h2>";
    
    $exitCode = $kernel->call('view:clear');
    echo "✓ view:clear<br>";
    
    $exitCode = $kernel->call('cache:clear');
    echo "✓ cache:clear<br>";
    
    $exitCode = $kernel->call('config:clear');
    echo "✓ config:clear<br>";
    
    echo "<br><strong>Success! All caches cleared.</strong><br>";
    echo "You can now delete this file (clear-cache.php) for security.";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
