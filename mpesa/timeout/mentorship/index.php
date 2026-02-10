<?php
/**
 * M-Pesa Timeout Forwarder for Mentorship Platform
 * Forwards M-Pesa timeout callbacks from main domain to subdomain
 * 
 * Deploy this file to: www.relearn.co.ke/public_html/mpesa/timeout/mentorship/index.php
 */

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/mpesa_timeout_forwarder.log');

// Set content type
header('Content-Type: application/json');

try {
    // Log the incoming timeout
    $rawInput = file_get_contents('php://input');
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] M-Pesa Timeout received for mentorship: $rawInput");
    
    // Get the enrollment ID from the URL path
    $requestUri = $_SERVER['REQUEST_URI'];
    $pathParts = explode('/', trim($requestUri, '/'));
    
    // Find enrollment ID (should be the last part of the path)
    $enrollmentId = null;
    for ($i = count($pathParts) - 1; $i >= 0; $i--) {
        if (is_numeric($pathParts[$i])) {
            $enrollmentId = $pathParts[$i];
            break;
        }
    }
    
    if (!$enrollmentId) {
        error_log("[$timestamp] Error: No valid enrollment ID found in URL: $requestUri");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid enrollment ID in URL'
        ]);
        exit;
    }
    
    // Prepare forwarding to mentorship subdomain
    $subdomainUrl = "https://mentorship.relearn.co.ke/api/mpesa/timeout/{$enrollmentId}";
    
    error_log("[$timestamp] Forwarding timeout to: $subdomainUrl");
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $subdomainUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawInput);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($rawInput),
        'User-Agent: M-Pesa-Timeout-Forwarder/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        error_log("[$timestamp] cURL Error: $curlError");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to forward timeout'
        ]);
        exit;
    }
    
    error_log("[$timestamp] Forwarding response - HTTP Code: $httpCode, Response: $response");
    
    // Return the response from subdomain
    http_response_code($httpCode);
    echo $response;
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] Exception in timeout forwarder: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>
