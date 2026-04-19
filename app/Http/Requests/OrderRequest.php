<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                           => ['required', 'array', 'min:1'],
            'items.*.product_id'              => ['required', 'string'],
            'items.*.qty'                     => ['required', 'integer', 'min:1'],
            'shipping_address'                => ['required', 'array'],
            'shipping_address.street'         => ['required', 'string'],
            'shipping_address.city'           => ['required', 'string'],
            'shipping_address.province'       => ['required', 'string'],
            'shipping_address.postal_code'    => ['required', 'string'],
            'notes'                           => ['nullable', 'string', 'max:500'],
            'payment_method'                  => ['nullable', 'string', 'in:cod,transfer,qris'],
        ];
    }
}
