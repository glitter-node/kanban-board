<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    public function view(User $user, Card $card): bool
    {
        return $card->board !== null && $card->board->hasActiveMember($user);
    }

    public function create(User $user, Board $board): bool
    {
        return in_array($board->roleFor($user), ['owner', 'editor'], true);
    }

    public function update(User $user, Card $card): bool
    {
        return $card->board !== null
            && in_array($card->board->roleFor($user), ['owner', 'editor'], true);
    }

    public function archive(User $user, Card $card): bool
    {
        return $this->update($user, $card);
    }

    public function assign(User $user, Card $card): bool
    {
        return $this->update($user, $card);
    }

    public function move(User $user, Card $card): bool
    {
        return $this->update($user, $card);
    }
}
