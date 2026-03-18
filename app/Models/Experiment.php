<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experiment extends Model
{
    protected $fillable = [
        'key',
        'name',
        'status',
        'primary_metric',
        'secondary_metrics',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'secondary_metrics' => 'array',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ExperimentVariant::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExperimentAssignment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ExperimentEvent::class);
    }
}
