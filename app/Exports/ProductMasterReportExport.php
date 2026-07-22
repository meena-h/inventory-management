<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductMasterReportExport implements WithMultipleSheets
{
    public function __construct(protected array $filters = []) {}

    public function sheets(): array
    {
        return [
            new ProductMasterSheetExport($this->filters),
            
        ];
    }
}