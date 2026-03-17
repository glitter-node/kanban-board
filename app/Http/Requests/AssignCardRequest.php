<?php

namespace App\Http\Requests;

use App\Models\Board;
use App\Models\Card;
use App\Models\User;
use App\Policies\CardPolicy;
use Illuminate\Foundation\Http\FormRequest;

class AssignCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Board|null $board */
        $board = $this->route('board');
        /** @var Card|null $card */
        $card = $this->route('card');

        if (! $user || ! $board || ! $card || ! app(CardPolicy::class)->assign($user, $card)) {
            return false;
        }

        return $card->belongsToBoard($board);
    }

    public function rules(): array
    {
        return [
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Board|null $board */
            $board = $this->route('board');
            $assigneeId = $this->input('assigned_user_id');

            if (! $board || $assigneeId === null) {
                return;
            }

            $assignee = User::query()->find($assigneeId);

            if ($assignee !== null && ! $assignee->isMemberOf($board)) {
                $validator->errors()->add('assigned_user_id', 'The selected assignee must be an active board member.');
            }
        });
    }
}
