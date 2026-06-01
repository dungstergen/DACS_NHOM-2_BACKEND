<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'price_monthly' => ['sometimes', 'numeric', 'min:0'],
            'deposit_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'area_sqm' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_occupants' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:available,occupied'],
            'amenities' => ['sometimes', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'images' => ['sometimes', 'array'],
            'images.*.url' => ['required_with:images', 'string', 'max:500'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
