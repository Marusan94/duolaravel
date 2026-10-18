<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Heart extends Model
{
    protected $fillable = ['user_id', 'current', 'max', 'next_regen_at'];

    protected $casts = ['next_regen_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
