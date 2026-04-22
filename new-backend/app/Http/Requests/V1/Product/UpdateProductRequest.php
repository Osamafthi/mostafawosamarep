<?php

namespace App\Http\Requests\V1\Product;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:200'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:active,inactive'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $id = (int) $this->route('id');
            $existing = $id > 0 ? Product::query()->find($id) : null;

            $price = $this->has('price') ? $this->input('price') : ($existing?->price);
            $discount = $this->has('discount_price') ? $this->input('discount_price') : ($existing?->discount_price);

            if ($price !== null && $discount !== null && (float) $discount >= (float) $price) {
                $validator->errors()->add('discount_price', 'The discount price must be less than the price.');
            }
        });
    }
}
