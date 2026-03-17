<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $table = 'boards';

    protected $fillable = [
        'owner_user_id',
        'type',
        'title',
        'description',
        'visibility',
        'is_archived',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
            'settings_json' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('order_key');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)->orderBy('order_key');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest('created_at');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(BoardMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_members')
            ->using(BoardMember::class)
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function activeMemberships(): HasMany
    {
        return $this->memberships()->where('status', 'active');
    }

    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where('owner_user_id', $userId);
    }

    public function scopeVisibleTo(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where(function (Builder $builder) use ($userId) {
            $builder->where('owner_user_id', $userId)
                ->orWhereHas('memberships', function (Builder $membershipQuery) use ($userId) {
                    $membershipQuery
                        ->where('user_id', $userId)
                        ->where('status', 'active');
                });
        });
    }

    public function scopeCollaborative(Builder $query): Builder
    {
        return $query->where('type', 'collaborative');
    }

    public function scopePersonal(Builder $query): Builder
    {
        return $query->where('type', 'personal');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function membershipFor(User|int $user): ?BoardMember
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $this->memberships
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first()
            ?? $this->memberships()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->first();
    }

    public function roleFor(User|int $user): ?string
    {
        return $this->membershipFor($user)?->role;
    }

    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return (int) $this->owner_user_id === (int) $userId;
    }

    public function hasActiveMember(User|int $user): bool
    {
        return $this->membershipFor($user) !== null;
    }
}
