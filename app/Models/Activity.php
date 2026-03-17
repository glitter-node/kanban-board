<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'board_id',
        'user_id',
        'action',
        'target_type',
        'target_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForBoard(Builder $query, Board|int $board): Builder
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return $query->where('board_id', $boardId);
    }

    public function scopeForEntity(Builder $query, string $type, int $id): Builder
    {
        return $query
            ->where('target_type', $type)
            ->where('target_id', $id);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function entityReference(): array
    {
        return [
            'type' => $this->target_type,
            'id' => $this->target_id,
        ];
    }
}
