<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkillProgress extends Model
{
    protected $table = 'user_skill_progress';

    protected $fillable = ['user_id', 'skill_id', 'crowns', 'level', 'last_practiced_at'];

    protected $casts = ['last_practiced_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
