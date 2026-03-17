<?php

namespace App\Http\Requests;

use App\Models\CardComment;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var CardComment|null $comment */
        $comment = $this->route('comment');

        return $user !== null && $comment !== null && app(CommentPolicy::class)->update($user, $comment);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'mentions_json' => ['nullable', 'array'],
        ];
    }
}
