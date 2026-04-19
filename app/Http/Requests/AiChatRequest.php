<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class AiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'         => ['nullable', 'string', 'max:2000'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'servings'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'conversation_id' => ['nullable', 'string'],
            'mode'            => ['nullable', 'in:chat,recipe'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->message) && !$this->hasFile('image')) {
                $v->errors()->add('message', 'Harus ada pesan teks atau gambar yang diupload.');
            }
        });
    }
}
