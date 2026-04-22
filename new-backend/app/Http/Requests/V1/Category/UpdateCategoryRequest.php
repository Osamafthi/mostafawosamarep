<?php

namespace App\Http\Requests\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
                Rule::unique('categories', 'name')->ignore($id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
