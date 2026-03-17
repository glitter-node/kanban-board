<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $table = 'cards';

    protected $fillable = [
        'board_id',
        'column_id',
        'creator_user_id',
        'title',
        'description',
        'assigned_user_id',
        'priority',
        'status',
        'order_key',
        'due_at',
        'started_at',
        'completed_at',
        'archived_at',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'due_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class)->latest('created_at');
    }

    public function scopeForBoard(Builder $query, Board|int $board): Builder
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return $query->where('board_id', $boardId);
    }

    public function scopeForColumn(Builder $query, Column|int $column): Builder
    {
        $columnId = $column instanceof Column ? $column->getKey() : $column;

        return $query->where('column_id', $columnId);
    }

    public function scopeAssignedTo(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where('assigned_user_id', $userId);
    }

    public function scopeDueSoon(Builder $query, int $hours = 24): Builder
    {
        return $query->whereNotNull('due_at')
            ->whereBetween('due_at', [now(), now()->addHours($hours)]);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_key');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', 'archived');
    }

    public function belongsToBoard(Board|int $board): bool
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return (int) $this->board_id === (int) $boardId;
    }

    public function isAssigned(): bool
    {
        return $this->assigned_user_id !== null;
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived' || $this->archived_at !== null;
    }
}
