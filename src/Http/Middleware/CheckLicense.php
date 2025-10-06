<?php

namespace Wontonee\Phonepe\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Wontonee\Phonepe\Services\LicenseService;
use Wontonee\Phonepe\Exceptions\InvalidLicenseException;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    /**
     * License service instance
     *
     * @var LicenseService
     */
    protected $licenseService;

    /**
     * Create a new middleware instance
     *
     * @param LicenseService $licenseService
     */
    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if license is valid
        if (!$this->licenseService->isValid()) {
            $status = $this->licenseService->getStatus();
            
            Log::channel('phonepe')->error('Payment blocked - Invalid license', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'message' => $status['message'] ?? 'Unknown error',
                'code' => $status['code'] ?? 'UNKNOWN',
            ]);

            // Throw exception with specific message
            throw new InvalidLicenseException(
                $status['message'] ?? 'PhonePe payment gateway license is invalid or expired. Please contact administrator.',
                $status['code'] ?? 'INVALID_LICENSE'
            );
        }

        // Log successful validation (for grace period warnings)
        $status = $this->licenseService->getStatus();
        if (isset($status['grace_period']) && $status['grace_period']) {
            Log::channel('phonepe')->warning('Payment processed in grace period', [
                'message' => $status['message'],
                'expires_at' => $status['grace_expires_at'] ?? 'Unknown',
            ]);
        }

        return $next($request);
    }
}
