<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'shipping_method' => $this->shipping_method,
            'shipping_cost' => $this->shipping_cost ?? 0,
            'total_price' => $this->total_price,
            'initial_payment' => $this->initial_payment ?? 0,
            'calculated_total_paid' => $this->calculated_total_paid ?? 0,
            'remaining_amount' => (($this->total_price ?? 0) + ($this->shipping_cost ?? 0)) - ($this->calculated_total_paid ?? 0),
            'created_at' => $this->created_at->timezone('Asia/Jakarta')->format('d F Y H:i'),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
