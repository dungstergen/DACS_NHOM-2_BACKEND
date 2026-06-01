<?php

namespace App\Http\Requests\Amenities;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $amenityId = $this->route('amenity')?->id;

        return [
            'name' => ['required', 'string', 'max:100', 'unique:amenities,name,'.$amenityId],
        ];
    }
}
