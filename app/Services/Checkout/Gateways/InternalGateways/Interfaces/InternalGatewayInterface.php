<?php

namespace App\Services\Checkout\Gateways\InternalGateways\Interfaces;

use App\Models\Payment;
use App\Models\PaymentGateway;

interface InternalGatewayInterface
{
    public function request(Payment $payment, $description = null);
    public function verify(Payment $payment);
    public function getGateway(): PaymentGateway;
}
