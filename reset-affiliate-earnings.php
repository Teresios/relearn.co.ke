<?php
/**
 * Manually Reset Available Earnings for a Specific Affiliate
 * Run: php reset-affiliate-earnings.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Affiliate email to reset
$targetEmail = 'andambiri3@gmail.com';

echo "🔄 Reset Affiliate Available Earnings\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Target Email: {$targetEmail}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Find user by email
    $user = \App\Models\User::where('email', $targetEmail)->first();
    
    if (!$user) {
        echo "❌ User not found in system\n";
        exit(1);
    }
    
    echo "✓ Found User: {$user->name}\n";
    
    // Find affiliate for this user
    $affiliate = \App\Models\Affiliate::where('user_id', $user->id)->first();
    
    if (!$affiliate) {
        echo "❌ No affiliate record found for user: {$user->name}\n";
        exit(1);
    }
    
    echo "✓ Found Affiliate: Code {$affiliate->code}\n\n";
    
    // Get pending commissions before reset
    $pendingCommissions = $affiliate->commissions()
        ->where('status', 'approved')
        ->where('payment_status', 'pending')
        ->get();
    
    $totalPendingEarnings = $pendingCommissions->sum('amount');
    
    echo "📊 Current Status:\n";
    echo "   • Total Pending Commissions: " . $pendingCommissions->count() . "\n";
    echo "   • Total Pending Earnings: Ksh " . number_format($totalPendingEarnings, 2) . "\n";
    
    if ($pendingCommissions->count() === 0) {
        echo "\n⚠️  No pending commissions found. Available earnings already at 0.\n";
        exit(0);
    }
    
    echo "\n💰 Resetting Earnings...\n";
    
    // Mark all pending commissions as paid
    $updatedCount = $affiliate->commissions()
        ->where('status', 'approved')
        ->where('payment_status', 'pending')
        ->update(['payment_status' => 'paid']);
    
    echo "   ✅ Updated {$updatedCount} pending commission(s) to paid status\n\n";
    
    // Verify the reset
    $remainingPending = $affiliate->commissions()
        ->where('status', 'approved')
        ->where('payment_status', 'pending')
        ->get();
    
    $newAvailableEarnings = $remainingPending->sum('amount');
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ RESET COMPLETED SUCCESSFULLY\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Affiliate: {$affiliate->user->name}\n";
    echo "Email: {$affiliate->user->email}\n";
    echo "Code: {$affiliate->code}\n\n";
    echo "📋 Summary:\n";
    echo "   • Commissions Reset: {$updatedCount}\n";
    echo "   • Amount Reset: Ksh " . number_format($totalPendingEarnings, 2) . "\n";
    echo "   • Available Earnings Now: Ksh " . number_format($newAvailableEarnings, 2) . "\n\n";
    echo "✓ Admin dashboard will now show 0 available earnings for this affiliate\n";
    echo "✓ The affiliate's dashboard will be updated accordingly\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";
exit(0);
