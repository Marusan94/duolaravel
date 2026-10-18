<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseOption extends Model
{
    protected $fillable = ['exercise_id', 'text', 'is_correct', 'position'];

    protected $casts = ['is_correct' => 'boolean'];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
