<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'provider' => $this->provider,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}
