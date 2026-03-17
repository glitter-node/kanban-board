<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardMemberRoleRequest extends FormRequest
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
            'role' => ['required', 'in:editor,viewer'],
        ];
    }
}
