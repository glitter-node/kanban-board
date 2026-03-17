<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class InviteBoardMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');

        return $user !== null && $board !== null && $user->can('delete', $board);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'in:owner,editor,viewer'],
        ];
    }
}
