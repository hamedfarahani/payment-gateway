<?php

namespace App\Services\Checkout\Gateways\InternalGateways;


use App\Exceptions\FailedPayment;
use App\Exceptions\OdinException;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Checkout\Gateways\InternalGateways\Interfaces\InternalGatewayInterface;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class ZibalInternalGatewayService implements InternalGatewayInterface
{
    private Client $restClient;
    private $response;
    private $trackId;
    private Payment $payment;

    public function __construct(private PaymentGateway $gateway)
    {
        $this->restClient = new Client([
            'base_uri'        => config('zibal.paymentBaseUri'),
            'http_errors'     => false,
            'timeout'         => config('zibal.timeout'),
            'connect_timeout' => config('zibal.connect_timeout')
        ]);
    }

    public function request(Payment $payment, $description = null)
    {
        $this->payment = $payment;
        $this->getTrackId();


        return sprintf('%s/%s/%s', config('zibal.paymentBaseUri'), config('zibal.paymentStart'), $this->trackId);
    }

    public function verify(Payment $payment)
    {
        // First : validate transaction
        $trackId = request()->query->get('trackId');
        $paymentStatus = request()->query->get('success');
        $response = $this->validatePayment($payment, $trackId, $paymentStatus);

        // Second : accept transaction
        $this->acceptPayment(request()->query->get('trackId'));
        Log::info('zibal [gatewayStatus]: '.json_encode($this->response));

        return [
            'trackId'  => $response['refNumber'],
            'paidAt'   => $response['paidAt'],
            'response' => $response,
        ];
    }

    private function validatePayment(Payment $payment, $trackId, $paymentStatus)
    {
        $validateData = [
            'merchant' => config('zibal.merchant'),
            'trackId'   => $trackId
        ];
        // check payment status is ok or not
        if ($paymentStatus == 0) {
            throw new FailedPayment();
        }
        // Get payment data from zibal
        $this->response = $this->restConnector('post', config('zibal.paymentInquiry'), $validateData);

        if (isset($this->response['result']) && $this->response['result'] !== 100) {
            throw new FailedPayment();
        }

        // not equal amount
        if ($payment->total_amount != ($this->response['amount'] / 10) ) {
            throw new OdinException(Response::HTTP_BAD_REQUEST, __('messages.zibal.error'));
        }
        return $this->response;
    }

    private function getTrackId()
    {
        $formParams = [
            "merchant"    => config('zibal.merchant'),
            "callbackUrl" => route('public.api.v1.payment.callback', $this->payment->id),
            "amount"      => ($this->payment->total_amount * 10),
        ];
        $this->restConnector('post', config('zibal.paymentTrackId'), $formParams);
        if (isset($this->response['result']) && $this->response['result'] !== 100) {
            throw new OdinException(Response::HTTP_BAD_REQUEST, json_encode($this->response));
        }

        $this->trackId = $this->response['trackId'];
    }

    private function restConnector($method, $path, $formParams = [])
    {
        try {
            $header = [
                'content-type'  => 'application/json',
                'Cache-Control' => 'no-cache'
            ];


            $result = $this->restClient->request($method, $path, [
                'headers' => $header,
                'body'    => json_encode($formParams)
            ]);
            $this->response = json_decode($result->getBody()->getContents(), true);
            // get error 400 or 500 from zibal
            return $this->response;
        } catch (Throwable $e) {
            makeException($e);
        }
    }

    private function acceptPayment($trackId)
    {
        $acceptData = [
            'merchant' => config('zibal.merchant'),
            'trackId'   => $trackId
        ];
        $this->restConnector('post', config('zibal.paymentAcceptUrl'), $acceptData);
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return PaymentGateway
     */
    public function getGateway(): PaymentGateway
    {
        return $this->gateway;
    }
}
