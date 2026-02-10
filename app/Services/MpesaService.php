<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MpesaService
{
    private $consumerKey;
    private $consumerSecret;
    private $environment;
    private $shortcode;
    private $passkey;
    private $callbackUrl;
    private $timeoutUrl;

    public function __construct()
    {
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->environment = config('mpesa.environment', 'sandbox');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->callbackUrl = config('mpesa.callback_url');
        $this->timeoutUrl = config('mpesa.timeout_url');
    }

    /**
     * Get the base URL based on environment.
     */
    private function getBaseUrl()
    {
        return $this->environment === 'production' || $this->environment === 'live'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Generate access token for M-Pesa API.
     */
    public function generateAccessToken()
    {
        try {
            $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification for testing
                'timeout' => 30,
            ])->withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->getBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials');

            if ($response->successful()) {
                $tokenData = $response->json();
                Log::info('M-Pesa access token generated successfully', [
                    'expires_in' => $tokenData['expires_in'] ?? null
                ]);
                return $tokenData['access_token'];
            }

            Log::error('Failed to generate M-Pesa access token', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error generating M-Pesa access token: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Generate password for STK Push.
     */
    private function generatePassword($timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = Carbon::now()->format('YmdHis');
        }
        return base64_encode($this->shortcode . $this->passkey . $timestamp);
    }

    /**
     * Get current timestamp in M-Pesa format.
     */
    public function getCurrentTimestamp()
    {
        return Carbon::now()->format('YmdHis');
    }

    /**
     * Initiate STK Push payment.
     */
    public function stkPush($phoneNumber, $amount, $accountReference, $transactionDesc = null)
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $timestamp = $this->getCurrentTimestamp();
            $password = $this->generatePassword($timestamp);

            // Ensure phone number is in correct format (254...)
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // --- Sanitize and limit the transactionDesc (remarks) field ---
            if ($transactionDesc) {
                // Remove non-alphanumeric and space characters
                $transactionDesc = preg_replace('/[^A-Za-z0-9 ]/', '', $transactionDesc);
                // Limit to 20 characters (adjust if your API allows more)
                $transactionDesc = substr($transactionDesc, 0, 20);
                // Fallback if empty
                if (empty(trim($transactionDesc))) {
                    $transactionDesc = 'Digital Access';
                }
            } else {
                $transactionDesc = 'Digital Access';
            }
            // -------------------------------------------------------------

            $requestData = [
                'BusinessShortCode' => (int) $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerBuyGoodsOnline',
                'Amount' => (float) $amount,
                'PartyA' => $phoneNumber,
                'PartyB' => 6890375,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc
            ];

            Log::info('M-Pesa STK Push Request', [
                'timestamp' => $timestamp,
                'phone' => $phoneNumber,
                'amount' => $amount,
                'reference' => $accountReference,
                'transactionDesc' => $transactionDesc
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/stkpush/v1/processrequest', $requestData);

            $responseData = $response->json() ?? [];

            Log::info('M-Pesa STK Push Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'CheckoutRequestID' => $responseData['CheckoutRequestID'],
                    'MerchantRequestID' => $responseData['MerchantRequestID'],
                    'ResponseDescription' => $responseData['ResponseDescription'],
                    'CustomerMessage' => $responseData['CustomerMessage'] ?? 'Payment request sent'
                ];
            }

            Log::error('STK Push failed', [
                'status' => $response->status(),
                'response' => $responseData
            ]);
            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? $responseData['ResponseDescription'] ?? 'Payment request failed',
                'error_code' => $responseData['ResponseCode'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Error initiating STK Push: ' . $e->getMessage(), [
                'phone' => $phoneNumber ?? null,
                'amount' => $amount
            ]);
            return ['success' => false, 'message' => 'Payment service temporarily unavailable'];
        }
    }

    /**
     * Query STK Push transaction status.
     */
    public function stkQuery($checkoutRequestId)
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $timestamp = $this->getCurrentTimestamp();
            $password = $this->generatePassword($timestamp);

            $requestData = [
                'BusinessShortCode' => (int) $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/stkpushquery/v1/query', $requestData);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error querying STK Push status: ' . $e->getMessage(), [
                'checkout_request_id' => $checkoutRequestId ?? 'unknown'
            ]);
            return ['success' => false, 'message' => 'Failed to query payment status'];
        }
    }

    /**
     * Format phone number to required format (254XXXXXXXXX).
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Handle different formats
        if (substr($phoneNumber, 0, 3) === '254') {
            return $phoneNumber;
        } elseif (substr($phoneNumber, 0, 1) === '0') {
            return '254' . substr($phoneNumber, 1);
        } elseif (substr($phoneNumber, 0, 1) === '7' || substr($phoneNumber, 0, 1) === '1') {
            return '254' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Validate phone number format.
     */
    public function isValidPhoneNumber($phoneNumber)
    {
        $formatted = $this->formatPhoneNumber($phoneNumber);
        return preg_match('/^254[0-9]{9}$/', $formatted);
    }

    /**
     * Format phone number (public accessor for controllers)
     */
    public function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Handle different formats
        if (substr($phoneNumber, 0, 3) === '254') {
            return $phoneNumber;
        } elseif (substr($phoneNumber, 0, 1) === '0') {
            return '254' . substr($phoneNumber, 1);
        } elseif (substr($phoneNumber, 0, 1) === '7' || substr($phoneNumber, 0, 1) === '1') {
            return '254' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Initiate B2C Payment (Business to Customer)
     */
    public function b2cPayment($phoneNumber, $amount, $remarks = 'Payment', $occasion = '')
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            $requestData = [
                'InitiatorName' => config('mpesa.initiator_name', 'testapi'),
                'SecurityCredential' => config('mpesa.security_credential'),
                'CommandID' => 'BusinessPayment',
                'Amount' => (int) $amount,
                'PartyA' => config('mpesa.b2c_shortcode', $this->shortcode),
                'PartyB' => $phoneNumber,
                'Remarks' => substr($remarks, 0, 100),
                'QueueTimeOutURL' => config('mpesa.b2c_timeout_url', $this->timeoutUrl),
                'ResultURL' => config('mpesa.b2c_result_url', $this->callbackUrl),
                'Occasion' => substr($occasion, 0, 100),
            ];

            Log::info('M-Pesa B2C Payment Request', [
                'phone' => $phoneNumber,
                'amount' => $amount,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/b2c/v1/paymentrequest', $requestData);

            $responseData = $response->json() ?? [];

            Log::info('M-Pesa B2C Payment Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'ConversationID' => $responseData['ConversationID'] ?? null,
                    'OriginatorConversationID' => $responseData['OriginatorConversationID'] ?? null,
                    'ResponseDescription' => $responseData['ResponseDescription'] ?? 'Request accepted',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? $responseData['ResponseDescription'] ?? 'B2C payment failed',
                'error_code' => $responseData['ResponseCode'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Error initiating B2C Payment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'B2C payment service unavailable'];
        }
    }

    /**
     * Initiate B2B Payment (Business to Business)
     */
    public function b2bPayment($receiverShortcode, $amount, $accountReference = '', $remarks = 'Payment')
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $requestData = [
                'Initiator' => config('mpesa.initiator_name', 'testapi'),
                'SecurityCredential' => config('mpesa.security_credential'),
                'CommandID' => 'BusinessPayBill',
                'SenderIdentifierType' => 4,
                'ReceiverIdentifierType' => 4,
                'Amount' => (int) $amount,
                'PartyA' => config('mpesa.b2b_shortcode', $this->shortcode),
                'PartyB' => $receiverShortcode,
                'AccountReference' => substr($accountReference, 0, 13),
                'Remarks' => substr($remarks, 0, 100),
                'QueueTimeOutURL' => config('mpesa.b2b_timeout_url', $this->timeoutUrl),
                'ResultURL' => config('mpesa.b2b_result_url', $this->callbackUrl),
            ];

            Log::info('M-Pesa B2B Payment Request', [
                'receiver' => $receiverShortcode,
                'amount' => $amount,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/b2b/v1/paymentrequest', $requestData);

            $responseData = $response->json() ?? [];

            Log::info('M-Pesa B2B Payment Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'ConversationID' => $responseData['ConversationID'] ?? null,
                    'OriginatorConversationID' => $responseData['OriginatorConversationID'] ?? null,
                    'ResponseDescription' => $responseData['ResponseDescription'] ?? 'Request accepted',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? $responseData['ResponseDescription'] ?? 'B2B payment failed',
                'error_code' => $responseData['ResponseCode'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Error initiating B2B Payment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'B2B payment service unavailable'];
        }
    }

    /**
     * Reverse a Transaction
     */
    public function reverseTransaction($transactionId, $amount, $remarks = 'Reversal')
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $requestData = [
                'Initiator' => config('mpesa.initiator_name', 'testapi'),
                'SecurityCredential' => config('mpesa.security_credential'),
                'CommandID' => 'TransactionReversal',
                'TransactionID' => $transactionId,
                'Amount' => (int) $amount,
                'ReceiverParty' => $this->shortcode,
                'ReceiverIdentifierType' => 11,
                'Remarks' => substr($remarks, 0, 100),
                'QueueTimeOutURL' => config('mpesa.reversal_timeout_url', $this->timeoutUrl),
                'ResultURL' => config('mpesa.reversal_result_url', $this->callbackUrl),
                'Occasion' => 'Reversal',
            ];

            Log::info('M-Pesa Reversal Request', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/reversal/v1/request', $requestData);

            $responseData = $response->json() ?? [];

            Log::info('M-Pesa Reversal Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'ConversationID' => $responseData['ConversationID'] ?? null,
                    'OriginatorConversationID' => $responseData['OriginatorConversationID'] ?? null,
                    'ResponseDescription' => $responseData['ResponseDescription'] ?? 'Reversal request accepted',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? $responseData['ResponseDescription'] ?? 'Reversal failed',
                'error_code' => $responseData['ResponseCode'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Error initiating Reversal: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Reversal service unavailable'];
        }
    }

    /**
     * Check Transaction Status
     */
    public function transactionStatus($transactionId, $identifierType = 'shortcode')
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $requestData = [
                'Initiator' => config('mpesa.initiator_name', 'testapi'),
                'SecurityCredential' => config('mpesa.security_credential'),
                'CommandID' => 'TransactionStatusQuery',
                'TransactionID' => $transactionId,
                'PartyA' => $this->shortcode,
                'IdentifierType' => $identifierType === 'shortcode' ? 4 : 1,
                'Remarks' => 'Transaction Status Query',
                'QueueTimeOutURL' => config('mpesa.status_timeout_url', $this->timeoutUrl),
                'ResultURL' => config('mpesa.status_result_url', $this->callbackUrl),
                'Occasion' => 'Status Query',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/transactionstatus/v1/query', $requestData);

            $responseData = $response->json() ?? [];

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'ConversationID' => $responseData['ConversationID'] ?? null,
                    'OriginatorConversationID' => $responseData['OriginatorConversationID'] ?? null,
                    'ResponseDescription' => $responseData['ResponseDescription'] ?? 'Query accepted',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? 'Status query failed',
            ];

        } catch (\Exception $e) {
            Log::error('Error querying transaction status: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Status query service unavailable'];
        }
    }

    /**
     * Register C2B URLs
     */
    public function registerC2BUrls()
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $requestData = [
                'ShortCode' => $this->shortcode,
                'ResponseType' => 'Completed',
                'ConfirmationURL' => config('mpesa.c2b_confirmation_url', $this->callbackUrl . '/c2b/confirm'),
                'ValidationURL' => config('mpesa.c2b_validation_url', $this->callbackUrl . '/c2b/validate'),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/c2b/v1/registerurl', $requestData);

            $responseData = $response->json() ?? [];

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'ResponseDescription' => $responseData['ResponseDescription'] ?? 'URLs registered',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? 'URL registration failed',
            ];

        } catch (\Exception $e) {
            Log::error('Error registering C2B URLs: ' . $e->getMessage());
            return ['success' => false, 'message' => 'URL registration service unavailable'];
        }
    }

    /**
     * Account Balance Query
     */
    public function accountBalance()
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            $requestData = [
                'Initiator' => config('mpesa.initiator_name', 'testapi'),
                'SecurityCredential' => config('mpesa.security_credential'),
                'CommandID' => 'AccountBalance',
                'PartyA' => $this->shortcode,
                'IdentifierType' => 4,
                'Remarks' => 'Balance Query',
                'QueueTimeOutURL' => config('mpesa.balance_timeout_url', $this->timeoutUrl),
                'ResultURL' => config('mpesa.balance_result_url', $this->callbackUrl),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/mpesa/accountbalance/v1/query', $requestData);

            $responseData = $response->json() ?? [];

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'ConversationID' => $responseData['ConversationID'] ?? null,
                    'OriginatorConversationID' => $responseData['OriginatorConversationID'] ?? null,
                    'ResponseDescription' => $responseData['ResponseDescription'] ?? 'Balance query accepted',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['errorMessage'] ?? 'Balance query failed',
            ];

        } catch (\Exception $e) {
            Log::error('Error querying account balance: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Balance query service unavailable'];
        }
    }
}
