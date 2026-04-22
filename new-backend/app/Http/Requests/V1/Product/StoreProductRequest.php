<?php

namespace App\Http\Requests\V1\Product;

use Illuminate\Contracts\Validation\Validator;
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
            'name' => ['required', 'string', 'min:2', 'max:200'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:active,inactive'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $price = $this->input('price');
            $discount = $this->input('discount_price');

            if ($price !== null && $discount !== null && (float) $discount >= (float) $price) {
                $validator->errors()->add('discount_price', 'The discount price must be less than the price.');
            }
        });
    }
}
