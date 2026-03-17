<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class ListCardsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');

        return $user !== null && $board !== null && $user->can('view', $board);
    }

    public function rules(): array
    {
        return [
            'column_id' => ['nullable', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:open,done,archived'],
            'due_soon_hours' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
