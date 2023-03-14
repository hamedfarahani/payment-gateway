<?php

namespace App\Services\Interfaces;


interface CheckoutServiceInterface
{
    public function checkout(array $data);

    public function callback($paymentId);
}
