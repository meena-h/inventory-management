<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');

        return [

            'name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplier),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}