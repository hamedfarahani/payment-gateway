<?php

namespace App\Services\Checkout\Gateways\InternalGateways;

use App\Exceptions\OdinException;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Checkout\Gateways\InternalGateways\Interfaces\InternalGatewayInterface;
use GuzzleHttp\Client;
use Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

class VandarInternalGatewayService implements InternalGatewayInterface
{
    private Client $restClient;
    private $response;
    private $token;
    private Payment $payment;

    public function __construct(private PaymentGateway $gateway)
    {
        $this->restClient = new Client([
            'base_uri'        => config('vandar.paymentBaseUri'),
            'http_errors'     => false,
            'timeout'         => config('vandar.timeout'),
            'connect_timeout' => config('vandar.connect_timeout')
        ]);
    }

    /**
     * @param Payment $payment
     * @param $description
     * @return string
     * @throws OdinException
     */
    public function request(Payment $payment, $description = null): string
    {
        $this->payment = $payment;

        // get token
        $this->getToken();

        // redirect to gateway
        return sprintf('%s/%s/%s', config('vandar.paymentBaseUri'), config('vandar.paymentGatewayUrl'), $this->token);
    }

    /**
     * @param Payment $payment
     * @param $token
     * @param $paymentStatus
     * @return array
     * @throws OdinException
     */
    public function verify(Payment $payment): array
    {
        // First : validate transaction
        $response = $this->validatePayment($payment, request()->query->get('token'), request()->query->get('payment_status'));

        // Second : accept transaction
        $this->acceptPayment(request()->query->get('token'));

        return [
            'trackId'  => $response['transId'],
            'paidAt'   => $response['paymentDate'],
            'response' => $response,
        ];
    }

    /**
     * @return void
     * @throws OdinException
     */
    private function getToken()
    {
        $tokenData = [
            'api_key'           => config('vandar.paymentApiKey'),
            'callback_url'      => route('public.api.v1.payment.callback', $this->payment->id),
            'factorNumber'      => $this->payment->id,
            'amount'            => (($this->payment->total_amount - $this->payment->used_wallet_amount) * 10),
            'valid_card_number' => (string)$this->payment->user_card_number,
            'mobile_number'     => $this->payment->user->mobile,
            'national_code'     => $this->payment->user->national_code,
        ];
        $this->restConnector('post', config('vandar.paymentTokenUrl'), $tokenData);

        $this->token = $this->response['token'];
    }

    /**
     * @param Payment $payment
     * @param $token
     * @param $paymentStatus
     * @return mixed
     * @throws OdinException
     */
    private function validatePayment(Payment $payment, $token, $paymentStatus)
    {
        $validateData = [
            'api_key' => config('vandar.paymentApiKey'),
            'token'   => $token
        ];

        // check payment status is ok or not
        if ($paymentStatus != 'OK') {
            throw new OdinException(Response::HTTP_BAD_REQUEST, __('messages.withdraw.vandarError'));
        }

        // Get payment data from vandar
        $this->restConnector('post', config('vandar.paymentValidationUrl'), $validateData);


        // not equal amount
        if (($payment->total_amount - $payment->used_wallet_amount) != ($this->response['realAmount'] / 10) ) {
            throw new OdinException(Response::HTTP_BAD_REQUEST, __('messages.withdraw.vandarError'));
        }

        // user pay with another card number
        $hashCard = hash('sha256' ,$payment->user_card_number);
        if (Str::upper($hashCard) != $this->response['cid']) {
            throw new OdinException(Response::HTTP_BAD_REQUEST, __('messages.withdraw.vandarError'));
        }

        // this payment is duplicate
        if ($this->response['code'] !== 1) {
            throw new OdinException(Response::HTTP_BAD_REQUEST, __('messages.withdraw.vandarError'));
        }

        return $this->response;
    }

    /**
     * @param $token
     * @return void
     * @throws OdinException
     */
    private function acceptPayment($token)
    {
        $acceptData = [
            'api_key' => config('vandar.paymentApiKey'),
            'token'   => $token
        ];
        $this->restConnector('post', config('vandar.paymentAcceptUrl'), $acceptData);
    }

    /**
     * @param $method
     * @param $path
     * @param $formParams
     * @return void
     * @throws OdinException
     */
    private function restConnector($method, $path, $formParams = []): void
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

            // get error 400 or 500 from vandar
            if (isset($this->response['status']) && $this->response['status'] === 0) {
                throw new OdinException(Response::HTTP_BAD_REQUEST, json_encode($this->response));
            }
        } catch (Throwable $e) {
            makeException($e);
        }
    }

    /**
     * @return PaymentGateway
     */
    public function getGateway(): PaymentGateway
    {
        return $this->gateway;
    }

    public function getResponse()
    {
        return $this->response;
    }
}

