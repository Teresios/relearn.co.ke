<?php
/**
 * Send Weekly Stats Emails to Specific Affiliates
 * Run: php send-weekly-stats-to-affiliates.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// List of affiliate emails to send to
$targetEmails = [
    'teresios.muriithi@giz.de',
    'morganmandela@gmail.com',
    'teresios73@gmail.com',
];

// Calculate this week's date range (Wednesday to Wednesday)
$today = now();
$dayOfWeek = $today->dayOfWeek; // 0 = Sunday, 3 = Wednesday

if ($dayOfWeek >= 3) {
    $weekStart = $today->copy()->startOfDay()->subDays($dayOfWeek - 3);
} else {
    $weekStart = $today->copy()->startOfDay()->subDays(7 - (3 - $dayOfWeek));
}
$weekEnd = $weekStart->copy()->addDays(7);

echo "📧 Sending Weekly Stats Emails\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Week Period: {$weekStart->format('Y-m-d')} to {$weekEnd->format('Y-m-d')}\n";
echo "Target Emails: " . count($targetEmails) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$successCount = 0;
$failedCount = 0;
$notFoundCount = 0;
$failedEmails = [];
$notFoundEmails = [];

foreach ($targetEmails as $email) {
    echo "Processing: {$email}...\n";
    
    try {
        // Find user by email
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            echo "  ❌ User not found in system\n\n";
            $notFoundCount++;
            $notFoundEmails[] = $email;
            continue;
        }
        
        // Find affiliate for this user
        $affiliate = \App\Models\Affiliate::where('user_id', $user->id)->first();
        
        if (!$affiliate) {
            echo "  ❌ No affiliate record found for user: {$user->name}\n\n";
            $notFoundCount++;
            $notFoundEmails[] = $email;
            continue;
        }
        
        echo "  ✓ Found: {$affiliate->user->name} (Code: {$affiliate->code})\n";
        
        // Calculate stats for this affiliate this week
        $weeklyCommissions = $affiliate->commissions()
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('affiliate_commissions.created_at', [$weekStart, $weekEnd])
            ->get();

        $commissionEarned = $weeklyCommissions->sum('amount');

        // Get this week's referrals with relationships loaded
        $weeklyReferrals = $affiliate->referrals()
            ->with(['order.product'])
            ->whereBetween('affiliate_referrals.created_at', [$weekStart, $weekEnd])
            ->get();

        $weeklyConversions = $weeklyReferrals->whereNotNull('purchased_at')->count();
        $weeklyClicks = $weeklyReferrals->count();

        // Get product details for this week's sales
        $productsSold = $weeklyReferrals
            ->whereNotNull('purchased_at')
            ->groupBy(function($item) {
                return $item->order?->product?->name ?? $item->product_name ?? 'Unknown Product';
            })
            ->map(function ($items) {
                $firstItem = $items->first();
                $productName = $firstItem->order?->product?->name ?? $firstItem->product_name ?? 'Unknown Product';
                // Use the sale price (order amount) not the actual product price
                $productPrice = $firstItem->order?->amount ?? $firstItem->order?->product?->price ?? 0;
                
                return [
                    'name' => $productName,
                    'count' => $items->count(),
                    'price' => $productPrice,
                ];
            })
            ->values();

        // Calculate total conversion value
        $totalConversionValue = $weeklyReferrals
            ->whereNotNull('purchased_at')
            ->sum(function ($referral) {
                return $referral->order?->amount ?? 0;
            });

        // Get total unique products sold
        $totalProductsSold = $productsSold->count();

        // Calculate conversion rate
        $conversionRate = $weeklyClicks > 0 
            ? round(($weeklyConversions / $weeklyClicks) * 100, 2)
            : 0;

        // Prepare payment data
        $paymentData = [
            'affiliate_code' => $affiliate->code,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'links_generated' => $affiliate->links()->count(),
            'total_sales' => $weeklyConversions,
            'products_sold' => $totalProductsSold,
            'products_detail' => $productsSold->toArray(),
            'total_conversion_value' => $totalConversionValue,
            'commission_earned' => round($commissionEarned, 2),
            'conversion_rate' => $conversionRate,
            'active_links' => $affiliate->links()->count(),
            'total_referrals' => $affiliate->referrals()->count(),
            'total_clicks_this_week' => $weeklyClicks,
            'payment_status' => $commissionEarned > 0 ? 'Eligible' : 'No Sales',
            'is_zero_commission' => $commissionEarned <= 0,
        ];

        // Display stats
        echo "  📊 Stats:\n";
        echo "     • Total Clicks: {$weeklyClicks}\n";
        echo "     • Conversions: {$weeklyConversions}\n";
        echo "     • Commission: Ksh " . number_format($commissionEarned, 2) . "\n";
        echo "     • Conversion Rate: {$conversionRate}%\n";

        // Send the email
        \Illuminate\Support\Facades\Mail::send(
            new \App\Mail\AffiliateWeeklyPaymentMail($affiliate, $paymentData)
        );

        // Mark all pending commissions as paid (all available earnings for the affiliate)
        // This resets the "Available Earnings" to 0 on their dashboard
        $updatedCount = $affiliate->commissions()
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->update(['payment_status' => 'paid']);

        echo "  ✅ Email sent successfully!\n";
        echo "  💰 Reset {$updatedCount} pending commission(s) to paid status\n\n";
        $successCount++;
        
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n\n";
        $failedCount++;
        $failedEmails[] = [
            'email' => $email,
            'error' => $e->getMessage(),
        ];
    }
}

// Summary report
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 SUMMARY REPORT\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Successful:  {$successCount}\n";
echo "❌ Failed:      {$failedCount}\n";
echo "⚠️  Not Found:   {$notFoundCount}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($notFoundCount > 0) {
    echo "\n⚠️  Users Not Found or No Affiliate:\n";
    foreach ($notFoundEmails as $email) {
        echo "   • {$email}\n";
    }
}

if ($failedCount > 0) {
    echo "\n❌ Failed Emails:\n";
    foreach ($failedEmails as $failed) {
        echo "   • {$failed['email']}: {$failed['error']}\n";
    }
}

if ($successCount > 0) {
    echo "\n✅ Successfully Sent To:\n";
    foreach ($targetEmails as $email) {
        $user = \App\Models\User::where('email', $email)->first();
        if ($user && \App\Models\Affiliate::where('user_id', $user->id)->exists()) {
            echo "   • {$email}\n";
        }
    }
}

echo "\n";
exit($failedCount > 0 ? 1 : 0);
