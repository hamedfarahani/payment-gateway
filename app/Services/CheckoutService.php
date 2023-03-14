<?php

namespace App\Services;

use App\Enum\PaymentEnum;
use App\Exceptions\FailedPayment;
use App\Exceptions\OdinException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Checkout\CheckoutType\InternalOnlineCheckoutService;
use App\Services\Checkout\Gateways\InternalGateways\InternalGatewayFactory;
use App\Services\Interfaces\CheckoutServiceInterface;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Throwable;

class CheckoutService implements CheckoutServiceInterface
{

    /**
     * @param array $data
     * @return array|void
     * @throws OdinException
     * @throws Throwable
     */
    public function checkout(array $data)
    {
        DB::beginTransaction();
        try {
            $internalGatewayClass = InternalGatewayFactory::makeInternalGateway();
            $checkoutTypeClass = new InternalOnlineCheckoutService($internalGatewayClass);

            // do checkout
            $payment = $checkoutTypeClass->checkout($data);

            DB::commit();

            return $payment;
        }
        catch (FailedPayment $failedPayment){
            DB::rollBack();
            makeException($failedPayment);
        }
        catch (Throwable $throwable) {
            DB::rollBack();
            makeException($throwable);
        }
    }

    /**
     * @param $paymentId
     * @return Builder
     * @throws Throwable
     */
    public function callback($paymentId)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::lockForUpdate()->find($paymentId);
            if ($payment->status !== PaymentEnum::PENDING) {
                throw new OdinException(Response::HTTP_BAD_REQUEST, __('messages.payment.payment_exists'));
            }
            $gatewayClass = InternalGatewayFactory::makeInternalGateway($payment->gatewayable_id);
            $onlinePayment = new InternalOnlineCheckoutService($gatewayClass);
            $onlinePayment->verifyPayment($payment);
            DB::commit();
        } catch (FailedPayment $exception) {
            $onlinePayment->updateFailedPayment($payment);
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();
        }
        return $payment;
    }

}
