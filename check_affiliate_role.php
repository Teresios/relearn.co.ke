<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::find(10);
if ($user) {
    echo "User: {$user->name}\n";
    echo "Roles: ";
    $roles = $user->getRoleNames();
    if ($roles->count() > 0) {
        echo $roles->implode(', ') . "\n";
    } else {
        echo "No roles assigned\n";
    }
    echo "Has affiliate role: " . ($user->hasRole('affiliate') ? 'YES ✓' : 'NO ✗') . "\n";
} else {
    echo "User not found\n";
}
