<?php

namespace App\Services\Checkout\Gateways\InternalGateways;

use App\Models\PaymentGateway;

class InternalGatewayFactory
{
    /**
     * @param $paymentGatewayId
     * @return VandarInternalGatewayService|ZibalInternalGatewayService
     */
    public static function makeInternalGateway($paymentGatewayId = null): VandarInternalGatewayService|ZibalInternalGatewayService
    {
        $paymentGateway = null;
        if (is_null($paymentGatewayId)){
            $paymentGateway = PaymentGateway::active()->first();
        }else{
            $paymentGateway = PaymentGateway::findOrFail($paymentGatewayId);
        }

        return match ($paymentGateway->slug) {
            'zibal' => new ZibalInternalGatewayService($paymentGateway),
            default => new VandarInternalGatewayService($paymentGateway)
        };
    }

}

