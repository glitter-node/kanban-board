<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Models\Card;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
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
            && app(CommentPolicy::class)->create($user, $board, $card);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'mentions_json' => ['nullable', 'array'],
        ];
    }
}
