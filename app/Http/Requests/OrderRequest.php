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
            'shipping_address.recipient_name' => ['required', 'string'],
            'shipping_address.phone'          => ['required', 'string'],
            'shipping_address.street'         => ['required', 'string'],
            'shipping_address.city'           => ['required', 'string'],
            'shipping_address.province'       => ['required', 'string'],
            'shipping_address.postal_code'    => ['required', 'string'],
            'shipping_address.lat'            => ['nullable', 'numeric'],
            'shipping_address.lng'            => ['nullable', 'numeric'],
            'address_id'                      => ['nullable', 'string'],
            'notes'                           => ['nullable', 'string', 'max:500'],
            'payment_method'                  => ['nullable', 'string', 'in:cod,bank_transfer,qris,gopay,ovo'],
            'delivery_fee'                    => ['nullable', 'numeric', 'min:0'],
            'promo_code'                      => ['nullable', 'string', 'max:50'],
        ];
    }
}
