<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'         => 'required|exists:categories,id',
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'sku'                 => 'nullable|string|unique:products,sku',
            'unit'                => 'sometimes|string|max:50',
            'price'               => 'required|numeric|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'is_active'           => 'sometimes|boolean',
            'supplier_ids'        => 'sometimes|array',
            'supplier_ids.*'      => 'exists:suppliers,id',
        ];
    }
}