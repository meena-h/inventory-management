<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [

            'category_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:categories,id',
            ],

            'name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'sku' => [
                'nullable',
                'string',
                Rule::unique('products', 'sku')->ignore($product),
            ],

            'unit' => [
                'sometimes',
                'string',
                'max:50',
            ],

            'price' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'numeric',
                'min:0',
            ],

            'low_stock_threshold' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'supplier_ids' => [
                'sometimes',
                'array',
            ],

            'supplier_ids.*' => [
                'exists:suppliers,id',
            ],
        ];
    }
}