<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'title'                        => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:200'],
            'description'                  => ['nullable', 'string'],
            'ingredients'                  => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'ingredients.*.name'           => ['required', 'string'],
            'ingredients.*.amount'         => ['required', 'string'],
            'ingredients.*.unit'           => ['nullable', 'string'],
            'steps'                        => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'steps.*.order'                => ['required', 'integer'],
            'steps.*.instruction'          => ['required', 'string'],
            'steps.*.duration_minutes'     => ['nullable', 'integer'],
            'category'                     => ['nullable', 'string', 'max:100'],
            'prep_time'                    => ['nullable', 'integer', 'min:0'],
            'cook_time'                    => ['nullable', 'integer', 'min:0'],
            'servings'                     => ['nullable', 'integer', 'min:1'],
            'difficulty'                   => ['nullable', 'in:easy,medium,hard'],
            'tags'                         => ['nullable', 'array'],
            'tags.*'                       => ['string'],
            'is_published'                 => ['boolean'],
            'images'                       => ['nullable', 'array'],
            'images.*'                     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
