<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['from_language_id', 'to_language_id', 'title', 'description', 'position', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function fromLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'from_language_id');
    }

    public function toLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'to_language_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderBy('position');
    }

    public function userCourses(): HasMany
    {
        return $this->hasMany(UserCourse::class);
    }
}
