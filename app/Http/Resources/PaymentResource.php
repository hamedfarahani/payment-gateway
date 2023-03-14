<?php

namespace App\Http\Resources;

use App\Enum\PaymentEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'amount' => $this->total_amount,
            'status' => $this->status,
            'redirectLink' => $this->status == PaymentEnum::PENDING ? json_decode($this->response, true)['redirectLink'] : null,
        ];
    }
}