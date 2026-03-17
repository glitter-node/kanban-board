<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Column;
use App\Models\User;

class ColumnPolicy
{
    public function view(User $user, Column $column): bool
    {
        return $column->board !== null && $column->board->hasActiveMember($user);
    }

    public function create(User $user, Board $board): bool
    {
        return in_array($board->roleFor($user), ['owner', 'editor'], true);
    }

    public function update(User $user, Column $column): bool
    {
        return $column->board !== null
            && in_array($column->board->roleFor($user), ['owner', 'editor'], true);
    }

    public function archive(User $user, Column $column): bool
    {
        return $this->update($user, $column);
    }

    public function reorder(User $user, Board $board): bool
    {
        return in_array($board->roleFor($user), ['owner', 'editor'], true);
    }
}
