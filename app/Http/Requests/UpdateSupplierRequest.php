<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');
        return [
            'name'      => 'sometimes|string|max:255',
            'email'     => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }
}