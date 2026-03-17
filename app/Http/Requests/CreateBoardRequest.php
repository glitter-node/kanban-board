<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class CreateBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', Board::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:personal,collaborative'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', 'in:private,workspace'],
            'settings_json' => ['nullable', 'array'],
        ];
    }
}
