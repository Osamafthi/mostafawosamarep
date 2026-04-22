<?php

namespace App\Http\Requests\V1\Upload;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Accept legacy `image` field name as an alias for `file`.
        if (! $this->hasFile('file') && $this->hasFile('image')) {
            $this->files->set('file', $this->file('image'));
        }
    }
}
