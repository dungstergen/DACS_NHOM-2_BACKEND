<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'area_sqm' => ['nullable', 'numeric', 'min:0'],
            'max_occupants' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:available,occupied'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'images' => ['nullable', 'array'],
            'images.*.url' => ['required_with:images', 'string', 'max:500'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
