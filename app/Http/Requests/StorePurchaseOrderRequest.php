<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'supplier_id'             => 'required|exists:suppliers,id',
            'ordered_at'              => 'required|date|before_or_equal:today',
            'note'                    => 'nullable|string|max:500',
            'products'                => 'required|array|min:1',
            'products.*.product_id'   => 'required|exists:products,id',
            'products.*.quantity'     => 'required|integer|min:1',
            'products.*.unit_price'   => 'required|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages (optional).
     */
    public function messages(): array
    {
        return [
            'products.required' => 'At least one product is required.',
            'products.array' => 'Products must be an array.',
            'products.min' => 'At least one product is required.',

            'products.*.product_id.required' => 'Product is required.',
            'products.*.product_id.exists' => 'Selected product does not exist.',

            'products.*.quantity.required' => 'Quantity is required.',
            'products.*.quantity.integer' => 'Quantity must be an integer.',
            'products.*.quantity.min' => 'Quantity must be at least 1.',

            'products.*.unit_price.required' => 'Unit price is required.',
            'products.*.unit_price.numeric' => 'Unit price must be a number.',
            'products.*.unit_price.min' => 'Unit price cannot be negative.',
        ];
    }
}