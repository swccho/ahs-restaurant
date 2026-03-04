<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->restaurant_id !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $restaurantId = $this->user()->restaurant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categories', 'name')->where('restaurant_id', $restaurantId),
            ],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
