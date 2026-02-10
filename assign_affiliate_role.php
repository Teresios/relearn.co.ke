<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::find(10);
if ($user) {
    $user->assignRole('affiliate');
    echo "✓ Affiliate role assigned to {$user->name}\n";
} else {
    echo "✗ User not found\n";
}
