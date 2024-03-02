<?php

namespace App\Http\Requests\Call;

use App\Enums\CustomerTitle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                new Enum(CustomerTitle::class),
            ],
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'requirement' => 'required|string|max:500',
            'phone_number' => 'required|starts_with:+',
            'caller_id' => 'nullable|exists:callers,id',
        ];
    }
}
