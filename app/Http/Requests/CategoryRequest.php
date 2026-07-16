<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [

            'name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->whereNull('deleted_at')->ignore($category),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

        ];
    }
}