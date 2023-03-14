<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CheckOutPaymentTest extends TestCase
{
//
//    /** @test */
//    public function it_returns_correct_payment_response()
//    {
//        $response = $this->post(route('payment.checkout'),[
//            'amount' => rand(10000,10000)
//        ]);
//
//        $response->assertStatus(201)
//            ->assertJsonStructure([
//                'data' => [
//                    'amount',
//                    'status',
//                    'redirectLink'
//                ]
//            ]);
//    }
    /** @test */

    public function check_callback_payment()
    {
        $payment = Payment::factory()->withStatus('PENDING')->withGatewayableId(2)->create();
        $url = route('public.api.v1.payment.callback', [$payment->id, 'trackId' => $payment->track_id,'success' => 1,'status' => 2]);
        $response = $this->get($url);
        dd($response);
    }

}
