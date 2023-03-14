<?php

namespace App\Services\Checkout\CheckoutType;

use App\Enum\PaymentEnum;
use App\Enum\TransactionEnum;
use App\Exceptions\FailedPayment;
use App\Exceptions\OdinException;
use App\Models\Payment;
use App\Services\Checkout\CheckoutableClass\Interfaces\CheckoutableInterface;
use App\Services\Checkout\Gateways\InternalGateways\Interfaces\InternalGatewayInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\WalletServiceInterface;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;
use Validator;

class InternalOnlineCheckoutService
{

    public function __construct(private InternalGatewayInterface    $gateway)
    {
    }

    public function checkout($data): Model|Payment
    {
        // create payment
        try {
            $gateway = $this->gateway->getGateway();

            // payment data
            $paymentData = [
                "total_amount"       => $data['amount'],
                "status"             => PaymentEnum::PENDING,
                "gatewayable_id"     => $gateway->id,
                "gatewayable_type"   => get_class($gateway),
            ];
            // save payment
            $payment = Payment::create($paymentData);

        } catch (Throwable $throwable) {
            makeException($throwable);
        }

        // create gateway link
        try {
            // save redirect link
            $redirectLink = $this->gateway->request($payment);
            $payment->response = json_encode(['redirectLinkBack' => $redirectLink,
                                              'redirectLink'     => route('web.bank.form.show', $payment->id)]);
            $payment->track_id =
            $payment->save();
        } catch (Throwable $e) {
            $payment->response = json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
            throw new FailedPayment($payment);
        }
        return $payment;
    }

    /**
     * @param Payment $payment
     * @return void
     * @throws OdinException
     * @throws Throwable
     */
    public function verifyPayment(Payment $payment): void
    {
        try {
            // verify payment
            $paymentData = $this->gateway->verify($payment);
        } catch (Throwable $exception) {
            Log::critical('payment.interanl.callback', (array)$exception);
            $payment->response = $this->gateway->getResponse();
            throw new FailedPayment($payment);
        }

        // update payment
        $this->updateSuccessPayment($payment, $paymentData);


    }

    /**
     * @param Payment $payment
     * @param $paymentData
     * @return void
     * @throws Throwable
     */
    public function updateSuccessPayment(Payment $payment, $paymentData): void
    {
        $payment->status = PaymentEnum::SUCCESS;
        $payment->response = $paymentData['response'];
        $payment->track_id = $paymentData['trackId'];
        $payment->save();
    }

    /**
     * @param Payment $payment
     * @return void
     * @throws Throwable
     */
    public function updateFailedPayment(Payment $payment): void
    {
        $payment->status = PaymentEnum::FAILED;
        $payment->save();
    }


}
