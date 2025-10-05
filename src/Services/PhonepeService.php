<?php

namespace Wontonee\Phonepe\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Phonepe Service
 * 
 * All logs are written to storage/logs/Phonepe.log
 */
class PhonepeService
{
    /**
     * API URLs for different environments
     */
    const SANDBOX_AUTH_URL = 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token';
    const PRODUCTION_AUTH_URL = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
    
    const SANDBOX_PAYMENT_URL = 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';
    const PRODUCTION_PAYMENT_URL = 'https://api.phonepe.com/apis/pg/checkout/v2/pay';

    /**
     * Get OAuth access token
     *
     * @return string
     * @throws Exception
     */
    public function getAccessToken()
    {
        $environment = core()->getConfigData('sales.payment_methods.Phonepe.environment') ?? 'sandbox';
        $clientId = core()->getConfigData('sales.payment_methods.Phonepe.client_id');
        $clientSecret = core()->getConfigData('sales.payment_methods.Phonepe.client_secret');
        $clientVersion = core()->getConfigData('sales.payment_methods.Phonepe.client_version') ?? '1';

        if (!$clientId || !$clientSecret) {
            throw new Exception('PhonePe credentials not configured');
        }

        $authUrl = $environment === 'production' 
            ? self::PRODUCTION_AUTH_URL 
            : self::SANDBOX_AUTH_URL;

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($authUrl, [
                    'client_id' => $clientId,
                    'client_version' => $clientVersion,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['access_token'])) {
                    Log::channel('phonepe')->info('OAuth token generated successfully', [
                        'expires_at' => $data['expires_at'] ?? null,
                    ]);
                    
                    return $data['access_token'];
                }
            }

            Log::channel('phonepe')->error('Failed to get access token', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new Exception('Failed to get PhonePe access token: ' . $response->body());

        } catch (Exception $e) {
            Log::channel('phonepe')->error('OAuth token generation failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create payment order
     *
     * @param array $orderData
     * @return array
     * @throws Exception
     */
    public function createPayment(array $orderData)
    {
        $accessToken = $this->getAccessToken();
        $environment = core()->getConfigData('sales.payment_methods.Phonepe.environment') ?? 'sandbox';
        
        $paymentUrl = $environment === 'production' 
            ? self::PRODUCTION_PAYMENT_URL 
            : self::SANDBOX_PAYMENT_URL;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $accessToken,
            ])
            ->timeout(30)
            ->post($paymentUrl, $orderData);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::channel('phonepe')->info('Payment order created successfully', [
                    'merchant_order_id' => $orderData['merchantOrderId'],
                    'phonepe_order_id' => $data['orderId'] ?? null,
                ]);

                return $data;
            }

            Log::channel('phonepe')->error('Payment creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'merchant_order_id' => $orderData['merchantOrderId'],
            ]);

            throw new Exception('Failed to create PhonePe payment: ' . $response->body());

        } catch (Exception $e) {
            Log::channel('phonepe')->error('Payment creation exception', [
                'error' => $e->getMessage(),
                'merchant_order_id' => $orderData['merchantOrderId'],
            ]);
            throw $e;
        }
    }

    /**
     * Check payment status
     *
     * @param string $merchantOrderId Merchant's order ID (not PhonePe's order ID)
     * @return array
     * @throws Exception
     */
    public function checkPaymentStatus(string $merchantOrderId)
    {
        $accessToken = $this->getAccessToken();
        $environment = core()->getConfigData('sales.payment_methods.Phonepe.environment') ?? 'sandbox';
        
        // PhonePe requires merchant order ID BEFORE /status in the endpoint URL
        $statusUrl = $environment === 'production' 
            ? "https://api.phonepe.com/apis/pg/checkout/v2/order/{$merchantOrderId}/status"
            : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$merchantOrderId}/status";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $accessToken,
            ])
            ->timeout(30)
            ->get($statusUrl);

            if ($response->successful()) {
                return $response->json();
            }

            Log::channel('phonepe')->error('Payment status check failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'merchant_order_id' => $merchantOrderId,
            ]);

            throw new Exception('Failed to check PhonePe payment status: ' . ($response->json()['message'] ?? 'Unknown error'));

        } catch (Exception $e) {
            Log::channel('phonepe')->error('Payment status check exception', [
                'error' => $e->getMessage(),
                'merchant_order_id' => $merchantOrderId,
            ]);
            throw $e;
        }
    }
}
