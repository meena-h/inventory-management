<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id'         => 'sometimes|exists:categories,id',
            'name'                => 'sometimes|string|max:255',
            'description'         => 'nullable|string',
            'sku'                 => 'nullable|string|unique:products,sku,' . $product->id,
            'unit'                => 'sometimes|string|max:50',
            'price'               => 'sometimes|numeric|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'is_active'           => 'sometimes|boolean',
            'supplier_ids'        => 'sometimes|array',
            'supplier_ids.*'      => 'exists:suppliers,id',
        ];
    }
}