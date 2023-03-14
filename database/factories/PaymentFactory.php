<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{

    protected $model = Payment::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ids = PaymentGateway::all()->pluck('id');
        return [
            'gatewayable_id' => $this->faker->randomElement($ids),
            'gatewayable_type' => PaymentGateway::class,
            'total_amount' => $this->faker->numberBetween(1000, 10000),
            'balance' => $this->faker->numberBetween(0, 1000),
            'status' => $this->faker->randomElement(['PENDING', 'SUCCESS', 'FAILED']),
            'track_id' => $this->faker->unique()->uuid,
        ];
    }

    public function withStatus($status)
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
            ];
        });
    }

    public function withGatewayableId($gatewayableId)
    {
        return $this->state(function (array $attributes) use ($gatewayableId) {
            return [
                'gatewayable_id' => $gatewayableId,
            ];
        });
    }
}
