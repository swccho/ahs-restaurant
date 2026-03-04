<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1200'],
            'type' => ['required', 'string', 'in:percentage,fixed,bogo,free_delivery'],
            'value' => [
                'nullable',
                'numeric',
                'required_if:type,percentage,fixed',
                'min:0',
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'coupon_code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('offers', 'coupon_code')
                    ->where('restaurant_id', $restaurantId)
                    ->whereNotNull('coupon_code'),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
            'banner_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('type');
            $value = $this->input('value');
            if (in_array($type, ['bogo', 'free_delivery'], true) && $value !== null && $value !== '') {
                $validator->errors()->add('value', 'Value must be empty for BOGO and free delivery offers.');
            }
            if ($type === 'percentage' && $value !== null && (float) $value > 100) {
                $validator->errors()->add('value', 'Percentage value must be between 1 and 100.');
            }
            if ($type === 'percentage' && $value !== null && (float) $value < 1) {
                $validator->errors()->add('value', 'Percentage value must be between 1 and 100.');
            }
        });
    }
}
