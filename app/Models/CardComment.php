<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardComment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'card_id',
        'user_id',
        'content',
        'mentions_json',
    ];

    protected function casts(): array
    {
        return [
            'mentions_json' => 'array',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForCard(Builder $query, Card|int $card): Builder
    {
        $cardId = $card instanceof Card ? $card->getKey() : $card;

        return $query->where('card_id', $cardId);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function hasMentions(): bool
    {
        return ! empty($this->mentions_json);
    }
}
