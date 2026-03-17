<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    use HasFactory;

    protected $table = 'columns';

    protected $fillable = [
        'board_id',
        'title',
        'type',
        'position',
        'wip_limit',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'wip_limit' => 'integer',
            'is_archived' => 'boolean',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)
            ->orderBy('position');
    }

    public function activeCards(): HasMany
    {
        return $this->cards()->where('status', '!=', 'archived');
    }

    public function scopeForBoard(Builder $query, Board|int $board): Builder
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return $query->where('board_id', $boardId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function belongsToBoard(Board|int $board): bool
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return (int) $this->board_id === (int) $boardId;
    }
}
