<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a card title.',
            'title.max' => 'The card title must be 255 characters or fewer.',
            'description.max' => 'The description must be 2000 characters or fewer.',
            'priority.in' => 'Please select a valid priority.',
            'due_date.date' => 'Please enter a valid date.',
        ];
    }
}
