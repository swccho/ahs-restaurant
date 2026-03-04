<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ToggleStaffRequest extends FormRequest
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
            'is_active' => ['required', 'boolean'],
        ];
    }
}
