<?php

namespace Mmrtonmoybd\Sslcommerz\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class Sslcommerz extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'sslcommerz';

    public function getRedirectUrl()
    {
        return route('sslcommerz.process');
    }

    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/money-transfer.png', 'shop');
    }
}
