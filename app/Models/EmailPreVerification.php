<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailPreVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'verified_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
