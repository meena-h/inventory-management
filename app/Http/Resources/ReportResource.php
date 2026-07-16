<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Product;
use App\Models\StockTransaction;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Product) {
            return [
                'product_id'          => $this->id,
                'product_code'        => $this->product_code,
                'name'                => $this->name,
                'sku'                 => $this->sku,
                'unit'                => $this->unit,
                'price'               => $this->price,
                'category'            => $this->category?->name ?? '-',
                'suppliers'           => $this->suppliers->pluck('name'),
                'current_stock'       => $this->current_stock,
                'low_stock_threshold' => $this->low_stock_threshold,
                'is_low_stock'        => $this->current_stock <= $this->low_stock_threshold,
                'is_active'           => $this->is_active,
            ];
        }

        // Stock Movement Report
        if ($this->resource instanceof StockTransaction) {
            return [
                'id' => $this->id,
                'transaction_date' => $this->transaction_date,
                'type' => $this->type,
                'quantity' => $this->quantity,
                'note' => $this->note,

                'product' => [
                     'id' => $this->product?->id,
                    'product_code' => $this->product?->product_code,
                    'name' => $this->product?->name,
                    'unit' => $this->product?->unit,
                ],

                'user' => [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                ],
            ];
        }

        return [];
    }
}