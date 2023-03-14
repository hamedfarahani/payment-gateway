<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payments = [
            [
                'title'      => 'زرین پال',
                'slug'       => 'zarinpal',
                'is_default' => false,
            ],
            [
                'title'      => 'زیبال',
                'slug'       => 'zibal',
                'is_default' => true,
            ],
            [
                'title'      => 'وندار',
                'slug'       => 'vandar',
                'is_default' => false,
            ]
        ];

        foreach ($payments as $payment) {
            PaymentGateway::updateOrCreate([
                'title' => $payment['title']
            ], $payment);
        }
    }
}
