<?php

namespace App\Services\Reports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductMasterReportService
{
    public function query(array $filters = []): Builder
    {
        return Product::query()
            ->with(['category:id,name'])
            ->when(
                isset($filters['price']) && is_array($filters['price']),
                fn (Builder $q) => $q->whereBetween('price', [
                    $filters['price'][0],
                    $filters['price'][1],
                ])
            )
            ->when(
                isset($filters['quantity']) && is_array($filters['quantity']),
                fn (Builder $q) => $q->whereBetween('current_stock', [
                    $filters['quantity'][0],
                    $filters['quantity'][1],
                ])
            )
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN))
            );
    }
}