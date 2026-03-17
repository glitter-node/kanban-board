<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Policies\ColumnPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CreateColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');

        return $user !== null && $board !== null && app(ColumnPolicy::class)->create($user, $board);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'type' => ['nullable', 'in:todo,doing,done,custom'],
            'wip_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
