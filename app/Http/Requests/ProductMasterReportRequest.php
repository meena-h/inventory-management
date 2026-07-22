<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductMasterReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'price_min'    => 'sometimes|nullable|numeric|min:0',
            'price_max'    => 'sometimes|nullable|numeric|min:0|gte:price_min',
            'quantity_min' => 'sometimes|nullable|integer|min:0',
            'quantity_max' => 'sometimes|nullable|integer|min:0|gte:quantity_min',
            'is_active'    => 'sometimes|nullable|boolean',
        ];
    }
}