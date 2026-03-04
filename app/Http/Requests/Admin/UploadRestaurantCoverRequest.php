<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadRestaurantCoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() && $this->user()?->restaurant_id !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}
