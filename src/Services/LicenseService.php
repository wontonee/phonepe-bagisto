<?php

namespace Wontonee\Phonepe\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LicenseService
{
    /**
     * License validation endpoint
     */
    const LICENSE_API_URL = 'https://myapps.wontonee.com/api/process-phonepe-data';

    /**
     * License cache key
     */
    const CACHE_KEY = 'phonepe_license_validation';

    /**
     * Cache duration (24 hours)
     */
    const CACHE_DURATION = 86400; // 24 hours in seconds

    /**
     * Grace period (3 days)
     */
    const GRACE_PERIOD = 259200; // 3 days in seconds

    /**
     * Validate license key
     *
     * @param string $licenseKey
     * @param bool $forceRefresh
     * @return array
     */
    public function validate(string $licenseKey, bool $forceRefresh = false): array
    {
        // Normalize license key (trim whitespace, convert to uppercase)
        $licenseKey = strtoupper(trim($licenseKey));
        
        if (empty($licenseKey)) {
            return [
                'valid' => false,
                'message' => 'License key is required',
                'code' => 'MISSING_LICENSE',
            ];
        }

        // Check format
        if (!$this->isValidFormat($licenseKey)) {
            return [
                'valid' => false,
                'message' => 'Invalid license key format. Must be exactly 16 uppercase hexadecimal characters (A-F, 0-9).',
                'code' => 'INVALID_FORMAT',
            ];
        }

        // Check cache first (unless force refresh)
        if (!$forceRefresh) {
            $cached = Cache::get($this->getCacheKey($licenseKey));
            if ($cached && isset($cached['valid'])) {
                Log::channel('phonepe')->info('License validation from cache', [
                    'license' => $this->maskLicense($licenseKey),
                    'valid' => $cached['valid'],
                ]);
                return $cached;
            }
        }

        // Remote validation
        return $this->validateRemote($licenseKey);
    }

    /**
     * Validate license format
     *
     * @param string $licenseKey
     * @return bool
     */
    protected function isValidFormat(string $licenseKey): bool
    {
        // Format: 16 characters, alphanumeric uppercase
        return preg_match('/^[A-F0-9]{16}$/', $licenseKey) === 1;
    }

    /**
     * Validate license against remote server
     *
     * @param string $licenseKey
     * @return array
     */
    protected function validateRemote(string $licenseKey): array
    {
        try {
            // Get domain from config (empty = trial mode)
            $domain = core()->getConfigData('sales.payment_methods.phonepe.domain') ?? '';
            
            Log::channel('phonepe')->info('Validating license with remote server', [
                'license' => $this->maskLicense($licenseKey),
                'domain' => $domain ?: 'trial (empty)',
                'api_url' => self::LICENSE_API_URL,
            ]);

            $requestData = [
                'license_key' => $licenseKey,
                'product_id' => 'PhonepeBagisto',
                'domain' => $domain,
            ];

            Log::channel('phonepe')->debug('License API request data', [
                'url' => self::LICENSE_API_URL,
                'data' => [
                    'license_key' => $this->maskLicense($licenseKey),
                    'product_id' => 'PhonepeBagisto',
                    'domain' => $domain ?: '(empty)',
                ],
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'PhonePe-Bagisto/1.0',
                ])
                ->post(self::LICENSE_API_URL, $requestData);

            Log::channel('phonepe')->info('License API response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'headers' => $response->headers(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if API returned an error
                if (isset($data['error'])) {
                    Log::channel('phonepe')->error('License validation failed - API error', [
                        'license' => $this->maskLicense($licenseKey),
                        'error' => $data['error'],
                    ]);
                    
                    return [
                        'valid' => false,
                        'message' => $data['error'],
                        'code' => 'INVALID_LICENSE',
                        'validated_at' => now()->toDateTimeString(),
                    ];
                }
                
                // Check if status is valid
                $isValid = isset($data['status']) && $data['status'] === 'valid';
                
                $result = [
                    'valid' => $isValid,
                    'message' => $isValid ? 'License is active and valid' : ($data['error'] ?? 'License validation failed'),
                    'license_type' => $data['license_type'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'remaining_usage' => $data['remaining_usage'] ?? null,
                    'domain' => $domain ?: 'trial',
                    'code' => $isValid ? 'VALID' : 'INVALID_LICENSE',
                    'validated_at' => now()->toDateTimeString(),
                ];

                // Cache the result
                if ($result['valid']) {
                    Cache::put(
                        $this->getCacheKey($licenseKey),
                        $result,
                        self::CACHE_DURATION
                    );
                }

                Log::channel('phonepe')->info('License validation response', [
                    'license' => $this->maskLicense($licenseKey),
                    'valid' => $result['valid'],
                    'message' => $result['message'],
                    'license_type' => $result['license_type'],
                    'expires_at' => $result['expires_at'],
                ]);

                return $result;
            }

            // Non-200 response - Log details and don't use grace period for 403/4xx errors
            Log::channel('phonepe')->error('License API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'license' => $this->maskLicense($licenseKey),
            ]);

            // For 403/401/400 errors, don't activate grace period - these are client errors
            if (in_array($response->status(), [400, 401, 403, 404])) {
                return [
                    'valid' => false,
                    'message' => 'License validation failed: ' . ($response->json()['error'] ?? 'Access denied'),
                    'code' => 'API_ERROR',
                    'validated_at' => now()->toDateTimeString(),
                ];
            }

            // Server error (5xx) - check grace period
            return $this->handleServerError($licenseKey);

        } catch (\Exception $e) {
            Log::channel('phonepe')->error('License validation failed', [
                'license' => $this->maskLicense($licenseKey),
                'error' => $e->getMessage(),
            ]);

            // Check grace period
            return $this->handleServerError($licenseKey);
        }
    }

    /**
     * Handle server error with grace period
     *
     * @param string $licenseKey
     * @return array
     */
    protected function handleServerError(string $licenseKey): array
    {
        $cacheKey = $this->getCacheKey($licenseKey) . '_grace';
        $gracePeriodStart = Cache::get($cacheKey);

        if (!$gracePeriodStart) {
            // Start grace period
            Cache::put($cacheKey, now()->timestamp, self::GRACE_PERIOD);
            
            Log::channel('phonepe')->warning('License server unreachable, grace period started', [
                'license' => $this->maskLicense($licenseKey),
                'grace_period_days' => 3,
            ]);

            return [
                'valid' => true,
                'message' => 'License server unreachable. Operating in grace period (3 days).',
                'code' => 'GRACE_PERIOD',
                'grace_period' => true,
                'grace_expires_at' => now()->addSeconds(self::GRACE_PERIOD)->toDateTimeString(),
            ];
        }

        // Check if grace period expired
        $gracePeriodEnd = $gracePeriodStart + self::GRACE_PERIOD;
        if (now()->timestamp > $gracePeriodEnd) {
            Log::channel('phonepe')->error('License grace period expired', [
                'license' => $this->maskLicense($licenseKey),
            ]);

            return [
                'valid' => false,
                'message' => 'License validation server unreachable and grace period expired. Please contact support.',
                'code' => 'GRACE_EXPIRED',
            ];
        }

        // Still in grace period
        $remainingSeconds = $gracePeriodEnd - now()->timestamp;
        $remainingDays = ceil($remainingSeconds / 86400);

        return [
            'valid' => true,
            'message' => "License server unreachable. Operating in grace period ({$remainingDays} days remaining).",
            'code' => 'GRACE_PERIOD',
            'grace_period' => true,
            'grace_expires_at' => Carbon::createFromTimestamp($gracePeriodEnd)->toDateTimeString(),
        ];
    }

    /**
     * Get current domain
     *
     * @return string
     */
    protected function getCurrentDomain(): string
    {
        // Get domain from config, fallback to auto-detection
        $domain = core()->getConfigData('sales.payment_methods.phonepe.domain');
        
        if (empty($domain)) {
            $domain = request()->getHost();
            
            // Remove www prefix
            $domain = preg_replace('/^www\./', '', $domain);
            
            // Remove port if present
            $domain = preg_replace('/:\d+$/', '', $domain);
        }
        
        return $domain;
    }

    /**
     * Get cache key for license
     *
     * @param string $licenseKey
     * @return string
     */
    protected function getCacheKey(string $licenseKey): string
    {
        $domain = core()->getConfigData('sales.payment_methods.phonepe.domain') ?? '';
        return self::CACHE_KEY . '_' . hash('sha256', $licenseKey . $domain);
    }

    /**
     * Mask license key for logging
     *
     * @param string $licenseKey
     * @return string
     */
    protected function maskLicense(string $licenseKey): string
    {
        if (strlen($licenseKey) < 8) {
            return '****';
        }
        
        return substr($licenseKey, 0, 4) . '********' . substr($licenseKey, -4);
    }

    /**
     * Clear license cache
     *
     * @param string $licenseKey
     * @return void
     */
    public function clearCache(string $licenseKey): void
    {
        Cache::forget($this->getCacheKey($licenseKey));
        Cache::forget($this->getCacheKey($licenseKey) . '_grace');
        
        Log::channel('phonepe')->info('License cache cleared', [
            'license' => $this->maskLicense($licenseKey),
        ]);
    }

    /**
     * Check if license is valid (simple check)
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $licenseKey = core()->getConfigData('sales.payment_methods.phonepe.license_key');
        
        if (empty($licenseKey)) {
            return false;
        }

        $result = $this->validate($licenseKey);
        
        return $result['valid'] ?? false;
    }

    /**
     * Get license validation status
     *
     * @return array
     */
    public function getStatus(): array
    {
        $licenseKey = core()->getConfigData('sales.payment_methods.phonepe.license_key');
        
        if (empty($licenseKey)) {
            return [
                'valid' => false,
                'message' => 'License key not configured',
                'code' => 'NOT_CONFIGURED',
            ];
        }

        return $this->validate($licenseKey);
    }
}
