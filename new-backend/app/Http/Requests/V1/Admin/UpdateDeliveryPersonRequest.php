<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // The route uses {id}; pull it so the unique rule can ignore the
        // current row when the email is unchanged.
        $id = $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:190',
                Rule::unique('delivery_persons', 'email')->ignore($id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
