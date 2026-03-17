<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'locale',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function ownedBoards(): HasMany
    {
        return $this->hasMany(Board::class, 'owner_user_id');
    }

    public function boardMemberships(): HasMany
    {
        return $this->hasMany(BoardMember::class);
    }

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'board_members')
            ->using(BoardMember::class)
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function assignedCards(): HasMany
    {
        return $this->hasMany(Card::class, 'assigned_user_id');
    }

    public function createdCards(): HasMany
    {
        return $this->hasMany(Card::class, 'creator_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'actor_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('last_seen_at');
    }

    public function scopeRecentlySeen(Builder $query): Builder
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(15));
    }

    public function isMemberOf(Board|int $board): bool
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return $this->boardMemberships()
            ->where('board_id', $boardId)
            ->where('status', 'active')
            ->exists();
    }

    public function membershipFor(Board|int $board): ?BoardMember
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return $this->boardMemberships()
            ->where('board_id', $boardId)
            ->where('status', 'active')
            ->first();
    }
}
