<?php
namespace App\Services\Checkout\CheckoutableClass\Interfaces;

use App\Models\Payment;

interface CheckoutableInterface
{
    public function getCheckoutUser();

    public function getCheckoutAmount();

    public function getCheckoutWallet();

    public function getCheckoutableModel();

    public function afterAcceptedCheckout();

    public function afterRejectCheckout(Payment $payment): void;

    public function getModelId();

    public function failedGatewayPayment($data);

}