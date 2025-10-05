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
        $imagePath = $this->getConfigData('image');

        // If custom image is uploaded via admin (stored in storage)
        if ($imagePath && \Illuminate\Support\Str::startsWith($imagePath, 'payment_methods/')) {
            return Storage::url($imagePath);
        }

        // If image path is set in config (vendor published asset)
        if ($imagePath) {
            return asset($imagePath);
        }

        // Fallback to default PhonePe icon (from published assets)
        return asset('vendor/wontonee/phonepe/images/phone.png');
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
