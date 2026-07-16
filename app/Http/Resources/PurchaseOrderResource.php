<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'ordered_at' => $this->ordered_at,
            'received_at' => $this->received_at,
            'note' => $this->note,

            'supplier' => $this->whenLoaded('supplier'),

            'user' => $this->whenLoaded('user'),

            'products' => $this->whenLoaded('products'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}