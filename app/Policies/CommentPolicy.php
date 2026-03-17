<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\User;

class CommentPolicy
{
    public function view(User $user, CardComment $comment): bool
    {
        return $comment->card !== null
            && $comment->card->board !== null
            && $comment->card->board->hasActiveMember($user);
    }

    public function create(User $user, Board $board, Card $card): bool
    {
        return $card->belongsToBoard($board)
            && in_array($board->roleFor($user), ['owner', 'editor'], true);
    }

    public function update(User $user, CardComment $comment): bool
    {
        return $comment->card !== null
            && $comment->card->board !== null
            && (
                (int) $comment->user_id === (int) $user->getKey()
                || in_array($comment->card->board->roleFor($user), ['owner', 'editor'], true)
            );
    }

    public function delete(User $user, CardComment $comment): bool
    {
        return $this->update($user, $comment);
    }
}
