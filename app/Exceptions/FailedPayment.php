<?php

namespace App\Exceptions;

use App\Models\Payment;
use Exception;

class FailedPayment extends Exception
{
    /**
     * @param Payment $payment
     */
    public function __construct(private Payment $payment)
    {
    }

    public function getPayment()
    {
        return $this->payment;
    }
}
