<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');

        return $user !== null && $board !== null && $user->can('update', $board);
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'in:personal,collaborative'],
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'visibility' => ['sometimes', 'in:private,workspace'],
            'settings_json' => ['nullable', 'array'],
        ];
    }
}
