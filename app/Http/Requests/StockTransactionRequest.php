<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
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
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'transaction_date' => 'required|date|before_or_equal:today',
            'note'             => 'nullable|string|max:500',
        ];
    }
}