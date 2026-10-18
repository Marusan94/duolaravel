<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = ['unit_id', 'title', 'slug', 'icon', 'position'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserSkillProgress::class);
    }
}
