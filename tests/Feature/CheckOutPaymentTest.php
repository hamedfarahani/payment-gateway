<?php

namespace Tests\Feature;

use App\Enum\PaymentEnum;
use App\Models\Payment;
use Tests\TestCase;

class CheckOutPaymentTest extends TestCase
{

    /** @test */
    public function it_returns_correct_payment_response()
    {
        $response = $this->post(route('payment.checkout'), [
            'amount' => rand(10000, 10000)
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'amount',
                    'status',
                    'redirectLink'
                ]
            ]);
    }

    /** @test */

    public function it_returns_failed_payment_response()
    {
        $payment = Payment::factory()->withStatus('PENDING')->withGatewayableId(2)->create();
        $url     = route('public.api.v1.payment.callback', [$payment->id, 'trackId' => $payment->track_id, 'success' => 1, 'status' => 2]);

        $response = $this->get($url);

        $response->assertStatus(200)
            ->assertExactJson([
                'data' => [
                    'amount'       => $payment->total_amount,
                    'status'       => PaymentEnum::FAILED,
                    'redirectLink' => null,
                ]
            ]);
    }

}
