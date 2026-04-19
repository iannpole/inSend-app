<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name'        => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'price'       => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'stock'       => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:0'],
            'category'    => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:100'],
            'unit'        => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
