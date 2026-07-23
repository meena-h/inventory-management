<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ProductMasterSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithColumnWidths
{
    public function __construct(protected Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Name',
            'SKU',
            'Category',
            'Unit',
            'Price',
            'Current Stock',
            'Low Stock Threshold',
            'Status',
        ];
    }

    public function map($product): array
    {
        return [
            $product->product_code,
            $product->name,
            $product->sku,
            $product->category->name ?? 'N/A',
            $product->unit,
            $product->price,
            $product->current_stock,
            $product->low_stock_threshold,
            $product->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function title(): string
    {
        return 'Product Master Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 15,
            'D' => 18,
            'E' => 10,
            'F' => 12,
            'G' => 15,
            'H' => 20,
            'I' => 12,
        ];
    }
}