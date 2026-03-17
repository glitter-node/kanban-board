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
        'actor_user_id',
        'entity_type',
        'entity_id',
        'action',
        'metadata_json',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function scopeForBoard(Builder $query, Board|int $board): Builder
    {
        $boardId = $board instanceof Board ? $board->getKey() : $board;

        return $query->where('board_id', $boardId);
    }

    public function scopeForEntity(Builder $query, string $entityType, int $entityId): Builder
    {
        return $query
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function entityReference(): array
    {
        return [
            'type' => $this->entity_type,
            'id' => $this->entity_id,
        ];
    }
}
