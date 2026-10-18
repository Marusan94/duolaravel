<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = ['skill_id', 'title', 'position', 'xp_reward'];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class)->orderBy('position');
    }

    public function userLessons(): HasMany
    {
        return $this->hasMany(UserLesson::class);
    }
}
