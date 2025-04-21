<?php
namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'amount' => $this->amount,
            'status' => $this->status,
            'proof' => $this->proof,
            'proof_url' => url('storage/' . $this->proof),
            'paid_at' => optional($this->paid_at)->format('d F Y H:i'),
            'created_at' => $this->created_at->format('d F Y H:i'),
        ];
    }
}
