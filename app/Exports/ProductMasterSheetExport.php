<?php

namespace App\Exports;

use App\Services\Reports\ProductMasterReportService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Excel;

class ProductMasterSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected array $filters = []) {}

    public function query()
    {
        return app(ProductMasterReportService::class)->query($this->filters);
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
            'Updated At',
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
            $product->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function title(): string
    {
        return 'Product Master Report';
    }
}