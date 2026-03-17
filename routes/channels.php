<?php

use App\Models\BoardMember;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('boards.{boardId}', function ($user, int $boardId) {
    return BoardMember::query()
        ->where('board_id', $boardId)
        ->where('user_id', $user->getKey())
        ->where('status', 'active')
        ->exists();
});

Broadcast::channel('boards.{boardId}.presence', function ($user, int $boardId) {
    $membership = BoardMember::query()
        ->select(['role'])
        ->where('board_id', $boardId)
        ->where('user_id', $user->getKey())
        ->where('status', 'active')
        ->first();

    if ($membership === null) {
        return false;
    }

    return [
        'id' => $user->getKey(),
        'name' => $user->name,
        'role' => $membership->role,
    ];
});

Broadcast::channel('users.{userId}', function ($user, int $userId) {
    return (int) $user->getKey() === (int) $userId;
});
