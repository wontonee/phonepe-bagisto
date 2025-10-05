<?php

namespace Wontonee\Phonepe\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Storage;

class Phonepe extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'phonepe';

    /**
     * Get redirect URL for PhonePe payment
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('phonepe.process');
    }

    /**
     * Get payment method image
     *
     * @return string|null
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        if ($url) {
            return Storage::url($url);
        }

        // Fallback to default PhonePe logo
        return asset('vendor/wontonee/phonepe/images/phonepe-logo.png');
    }

    /**
     * Check if payment method is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        // Check if required credentials are configured
        $clientId = $this->getConfigData('client_id');
        $clientSecret = $this->getConfigData('client_secret');

        return !empty($clientId) && !empty($clientSecret);
    }
}
