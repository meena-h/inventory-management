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
            'price'             => 'sometimes|nullable|array|size:2',
            'price.0'           => 'required_with:price|numeric|min:0',
            'price.1'           => 'required_with:price|numeric|min:0|gte:price.0',

            'quantity'          => 'sometimes|nullable|array|size:2',
            'quantity.0'        => 'required_with:quantity|integer|min:0',
            'quantity.1'        => 'required_with:quantity|integer|min:0|gte:quantity.0',

            'is_active'         => 'sometimes|nullable|boolean',
        ];
    }
}