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
            'name'                       => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:200'],
            'slug'                       => ['nullable', 'string', 'max:200'],
            'description'                => ['nullable', 'string'],
            'category_slug'              => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:100'],
            'base_price'                 => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'sale_price'                 => ['nullable', 'numeric', 'min:0'],
            'discount_info'              => ['nullable', 'array'],
            'discount_info.is_active'    => ['nullable', 'boolean'],
            'discount_info.percentage'   => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_info.campaign_name'=> ['nullable', 'string', 'max:100'],
            'discount_info.start_date'   => ['nullable', 'date'],
            'discount_info.end_date'     => ['nullable', 'date', 'after_or_equal:discount_info.start_date'],
            'stock_quantity'             => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:0'],
            'low_stock_threshold'        => ['nullable', 'integer', 'min:0'],
            'unit'                       => ['nullable', 'string', 'max:50'],
            'is_active'                  => ['boolean'],
            'tags'                       => ['nullable', 'array'],
            'tags.*'                     => ['string'],
            'attributes'                 => ['nullable', 'array'],
            'images'                     => ['nullable', 'array'],
            'images.*'                   => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
