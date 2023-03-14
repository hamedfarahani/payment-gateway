<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\Interfaces\CheckoutServiceInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class BankCheckoutController extends Controller
{
    public function __construct(private CheckoutServiceInterface $checkoutService)
    {
    }

    /**
     * @param $paymentId
     * @return Application|Factory|View
     */
    public function showForm($paymentId): View|Factory|Application
    {
        return view('gateway.bankRedirect', ['paymentId' => $paymentId]);
    }

    /**
     * @param $paymentId
     * @return RedirectResponse
     */
    public function redirect($paymentId): RedirectResponse
    {
        $payment = Payment::findOrFail($paymentId);
        $response = json_decode($payment->response, true);
        if (is_array($response) && isset($response['redirectLinkBack'])) {
            return Redirect::to($response['redirectLinkBack']);
        }
        return Redirect::to(paymentRedirectFront('failed', $payment));

    }

    /**
     * @param $paymentId
     * @return RedirectResponse
     */
    public function callback($paymentId)
    {
        $payment = $this->checkoutService->callback($paymentId);
        return new PaymentResource($payment);
    }
}