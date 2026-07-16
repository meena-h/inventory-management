<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'product_code' => $this->product_code,
            'name' => $this->name,
            'key' => $this->key,
            'description' => $this->description,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'price' => $this->price,
            'current_stock' => $this->current_stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_active' => $this->is_active,

            'category' => $this->whenLoaded('category'),

            'suppliers' => $this->whenLoaded('suppliers'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}