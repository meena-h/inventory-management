<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductMasterReport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(protected Builder $query) {}

    public function sheets(): array
    {
        return [
            new ProductMasterSheet($this->query),
        ];
    }
}