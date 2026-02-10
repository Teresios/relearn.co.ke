<?php
/**
 * Guest User Payment Flow Test Script
 * 
 * This script tests the complete guest checkout → payment → email flow
 * Usage: php artisan tinker < test_guest_flow.php
 */

// Test 1: Check session configuration
echo "=== Test 1: Session Configuration ===\n";
echo "Session Driver: " . config('session.driver') . "\n";
echo "Session Lifetime: " . config('session.lifetime') . "\n";
echo "Session Cookie Name: " . config('session.cookie') . "\n";

// Test 2: Create a test order
echo "\n=== Test 2: Creating Test Order ===\n";

$order = \App\Models\Order::create([
    'user_id' => null,  // Guest user
    'product_id' => 1,  // Assuming product ID 1 exists
    'payment_method' => 'mpesa',
    'status' => \App\Models\Order::STATUS_PENDING,
    'amount' => 500,
    'payment_phone' => '+254712345678',
    'customer_email' => 'test@example.com',
    'customer_phone' => '+254712345678',
    'customer_name' => 'Test Guest',
    'download_token' => bin2hex(random_bytes(16)),
    'thank_you_sent' => false,
]);

echo "Created order ID: " . $order->id . "\n";
echo "Download token: " . $order->download_token . "\n";
echo "Customer email: " . $order->customer_email . "\n";

// Test 3: Check if session can be set and persisted
echo "\n=== Test 3: Testing Session Persistence ===\n";

// Simulate setting session like in store() method
$sessionKey = 'guest_order_' . $order->id;
session()->put($sessionKey, $order->payment_phone);
session()->save();

echo "Session key: $sessionKey\n";
echo "Session value set to: " . $order->payment_phone . "\n";
echo "Session value retrieved: " . session($sessionKey) . "\n";
echo "Session ID: " . session()->getId() . "\n";
echo "Session Cookie Path: " . config('session.path') . "\n";

// Test 4: Check Download record
echo "\n=== Test 4: Creating Download Record ===\n";

$download = \App\Models\Download::create([
    'user_id' => null,
    'product_id' => $order->product_id,
    'order_id' => $order->id,
    'token' => $order->download_token,
    'expires_at' => now()->addDays(7),
    'download_count' => 0,
    'max_downloads' => 3,
]);

echo "Created download record ID: " . $download->id . "\n";
echo "Download token matches order token: " . ($download->token === $order->download_token ? 'YES' : 'NO') . "\n";

// Test 5: Test email sending
echo "\n=== Test 5: Testing Email Sending ===\n";

try {
    $product = $order->product;
    $token = $order->download_token;
    
    $epubDownloadUrl = $product->hasPdf() ? route('downloads.serve-file', [$token, 'epub']) : null;
    $pdfDownloadUrl = $product->hasPdf() ? route('downloads.serve-file', [$token, 'pdf']) : null;
    $zipDownloadUrl = $product->hasZip() ? route('downloads.serve-file', [$token, 'zip']) : null;
    
    echo "ePub URL: " . ($epubDownloadUrl ?? 'N/A') . "\n";
    echo "PDF URL: " . ($pdfDownloadUrl ?? 'N/A') . "\n";
    echo "ZIP URL: " . ($zipDownloadUrl ?? 'N/A') . "\n";
    
    // Send test email
    \Illuminate\Support\Facades\Mail::to($order->customer_email)->send(
        new \App\Mail\ProductDownloadLinkMail(
            (object)['name' => 'Test Guest', 'email' => $order->customer_email, 'id' => null],
            $product,
            $epubDownloadUrl,
            $pdfDownloadUrl,
            $zipDownloadUrl
        )
    );
    
    echo "Email sent successfully to: " . $order->customer_email . "\n";
} catch (\Exception $e) {
    echo "Email sending failed: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

// Test 6: Check routes exist
echo "\n=== Test 6: Checking Routes ===\n";

try {
    $paymentStatusRoute = route('orders.payment-status', ['order' => $order->id, 'token' => $order->download_token]);
    echo "payment-status route: " . $paymentStatusRoute . "\n";
} catch (\Exception $e) {
    echo "payment-status route ERROR: " . $e->getMessage() . "\n";
}

try {
    $downloadGuestRoute = route('orders.download-guest', ['order' => $order->id, 'token' => $order->download_token]);
    echo "download-guest route: " . $downloadGuestRoute . "\n";
} catch (\Exception $e) {
    echo "download-guest route ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Test order created with ID: $order->id\n";
echo "You can now test the payment flow by visiting the routes above in your browser.\n";
?>
