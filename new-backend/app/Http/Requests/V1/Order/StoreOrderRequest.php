<?php

namespace App\Http\Requests\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:200'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'shipping_address' => ['required', 'string', 'min:5'],

            // Optional GPS share captured by the checkout's "Share my
            // precise location" button. Both must be provided together
            // — `required_with` ensures we never store a half-pair.
            'customer_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:customer_longitude'],
            'customer_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:customer_latitude'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
