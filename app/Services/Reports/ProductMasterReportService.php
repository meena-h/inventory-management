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
                isset($filters['price_min']),
                fn (Builder $q) => $q->where('price', '>=', $filters['price_min'])
            )
            ->when(
                isset($filters['price_max']),
                fn (Builder $q) => $q->where('price', '<=', $filters['price_max'])
            )
            ->when(
                isset($filters['quantity_min']),
                fn (Builder $q) => $q->where('current_stock', '>=', $filters['quantity_min'])
            )
            ->when(
                isset($filters['quantity_max']),
                fn (Builder $q) => $q->where('current_stock', '<=', $filters['quantity_max'])
            )
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN))
            );
    }
}