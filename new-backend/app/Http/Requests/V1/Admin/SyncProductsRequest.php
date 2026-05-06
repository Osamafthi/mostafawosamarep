<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1'],
            'products.*.barcode' => ['required', 'string', 'max:100'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.price' => ['nullable', 'numeric', 'min:0'],
            'products.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'products.*._change' => ['sometimes', 'string', 'in:new,updated'],
        ];
    }
}
