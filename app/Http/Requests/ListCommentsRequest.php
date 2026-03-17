<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;

class ListCommentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');
        /** @var Card|null $card */
        $card = $this->route('card');

        return $user !== null
            && $board !== null
            && $card !== null
            && $card->belongsToBoard($board)
            && $user->can('view', $board);
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
