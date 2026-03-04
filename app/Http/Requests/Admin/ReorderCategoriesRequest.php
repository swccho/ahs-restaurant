<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class ReorderCategoriesRequest extends FormRequest
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
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:categories,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Ensure all category IDs belong to the current restaurant.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $restaurantId = $this->user()->restaurant_id;
            $ids = collect($this->input('items', []))->pluck('id')->unique()->values()->all();
            $count = Category::where('restaurant_id', $restaurantId)->whereIn('id', $ids)->count();
            if ($count !== count($ids)) {
                $validator->errors()->add('items', 'One or more category IDs do not belong to your restaurant.');
            }
        });
    }
}
