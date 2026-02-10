<?php
/**
 * M-Pesa Callback Forwarder Test Script
 * Tests the callback forwarding mechanism
 * 
 * Deploy this file to: www.relearn.co.ke/public_html/mpesa/test-forward.php
 */

// Set content type for web viewing
header('Content-Type: text/plain');

echo "M-Pesa Callback Forwarder Test\n";
echo "==============================\n\n";

// Test data simulating M-Pesa callback
$testData = json_encode([
    'Body' => [
        'stkCallback' => [
            'MerchantRequestID' => 'TEST-' . time(),
            'CheckoutRequestID' => 'ws_CO_TEST_' . time(),
            'ResultCode' => 0,
            'ResultDesc' => 'The service request is processed successfully.',
            'CallbackMetadata' => [
                'Item' => [
                    [
                        'Name' => 'Amount',
                        'Value' => 100
                    ],
                    [
                        'Name' => 'MpesaReceiptNumber',
                        'Value' => 'TEST' . time()
                    ],
                    [
                        'Name' => 'PhoneNumber',
                        'Value' => 254723071290
                    ]
                ]
            ]
        ]
    ]
]);

echo "Test Data:\n";
echo $testData . "\n\n";

// Test callback forwarding
echo "Testing callback forwarding...\n";
echo "URL: https://www.relearn.co.ke/mpesa/callback/mentorship/1\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.relearn.co.ke/mpesa/callback/mentorship/1');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: M-Pesa-Test-Client/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "\nCallback Test Results:\n";
echo "----------------------\n";
echo "HTTP Code: {$httpCode}\n";
if ($curlError) {
    echo "cURL Error: {$curlError}\n";
}
echo "Response: {$response}\n\n";

// Test timeout forwarding
echo "Testing timeout forwarding...\n";
echo "URL: https://www.relearn.co.ke/mpesa/timeout/mentorship/1\n";

$timeoutData = json_encode([
    'Body' => [
        'stkCallback' => [
            'MerchantRequestID' => 'TIMEOUT-' . time(),
            'CheckoutRequestID' => 'ws_CO_TIMEOUT_' . time(),
            'ResultCode' => 1032,
            'ResultDesc' => 'Request cancelled by user'
        ]
    ]
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.relearn.co.ke/mpesa/timeout/mentorship/1');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $timeoutData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: M-Pesa-Test-Client/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "\nTimeout Test Results:\n";
echo "---------------------\n";
echo "HTTP Code: {$httpCode}\n";
if ($curlError) {
    echo "cURL Error: {$curlError}\n";
}
echo "Response: {$response}\n\n";

echo "Test completed!\n";
echo "Check the logs at /tmp/mpesa_callback_forwarder.log and /tmp/mpesa_timeout_forwarder.log\n";
?>
