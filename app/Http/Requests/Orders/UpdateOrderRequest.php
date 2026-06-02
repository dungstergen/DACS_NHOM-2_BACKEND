<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,paid,cancelled,refunded'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
