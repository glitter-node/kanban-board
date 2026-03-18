<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Models\Column;
use App\Policies\CardPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CreateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');
        $columnId = $this->input('column_id');
        $column = $columnId ? Column::query()->find($columnId) : null;

        return $user !== null
            && $board !== null
            && $column !== null
            && $column->belongsToBoard($board)
            && app(CardPolicy::class)->create($user, $board);
    }

    public function rules(): array
    {
        return [
            'column_id' => ['required', 'integer', 'exists:columns,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:255'],
            'status' => ['nullable', 'in:open,done,archived'],
            'blocked' => ['nullable', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'required_if:blocked,true'],
            'due_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
