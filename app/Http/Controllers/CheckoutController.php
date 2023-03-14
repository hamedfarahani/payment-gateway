<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\PaymentResource;
use App\Services\Interfaces\CheckoutServiceInterface;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutServiceInterface $checkoutService)
    {

    }

    public function checkout(CheckoutRequest $checkoutRequest)
    {
        $payment = $this->checkoutService->checkout($checkoutRequest->validated());
        return new PaymentResource($payment);
    }

    public function verify($paymentId): void
    {
        $this->checkoutService->callback($paymentId);
    }

    public function callback($paymentId): JsonResponse
    {
        $this->checkoutService->callback($paymentId);
        return $this->sendSuccess();
    }
}
