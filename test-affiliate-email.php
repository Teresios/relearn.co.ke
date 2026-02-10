<?php
/**
 * Test affiliate payment email sending
 * Run: php test-affiliate-email.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

$affiliate = \App\Models\Affiliate::find(14);

if (!$affiliate) {
    echo "Affiliate not found!\n";
    exit(1);
}

$paymentData = [
    'affiliate_code' => $affiliate->code,
    'links_generated' => $affiliate->links()->count(),
    'total_sales' => 1,
    'products_sold' => 1,
    'products_detail' => [
        [
            'name' => 'Test Product',
            'count' => 1,
            'price' => 149
        ]
    ],
    'total_conversion_value' => 149,
    'commission_earned' => 44.70,
    'conversion_rate' => 100,
    'active_links' => $affiliate->links()->count(),
    'total_referrals' => 1,
    'payment_status' => 'Paid'
];

try {
    \Illuminate\Support\Facades\Mail::send(
        new \App\Mail\AffiliateWeeklyPaymentMail($affiliate, $paymentData)
    );
    echo "✅ Email sent successfully to: " . $affiliate->user->email . "\n";
    echo "Affiliate: " . $affiliate->user->name . "\n";
    echo "Amount: Ksh " . number_format($paymentData['commission_earned'], 2) . "\n";
} catch (\Exception $e) {
    echo "❌ Error sending email: " . $e->getMessage() . "\n";
    echo "Error details: " . $e->getFile() . " at line " . $e->getLine() . "\n";
    exit(1);
}
