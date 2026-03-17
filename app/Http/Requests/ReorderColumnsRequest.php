<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Policies\ColumnPolicy;
use Illuminate\Foundation\Http\FormRequest;

class ReorderColumnsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');

        return $user !== null && $board !== null && app(ColumnPolicy::class)->reorder($user, $board);
    }

    public function rules(): array
    {
        return [
            'column_ids' => ['required', 'array', 'min:1'],
            'column_ids.*' => ['integer', 'distinct'],
        ];
    }
}
