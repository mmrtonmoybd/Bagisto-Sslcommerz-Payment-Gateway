<?php

namespace Mmrtonmoybd\Sslcommerz\Payment;

use Webkul\Payment\Payment\Payment;

class Sslcommerz extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'sslcommerz';

    public function getRedirectUrl()
    {
        return route('sslcommerz.process');
        
    }
}