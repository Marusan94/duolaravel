<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'flag'];

    public function coursesFrom(): HasMany
    {
        return $this->hasMany(Course::class, 'from_language_id');
    }

    public function coursesTo(): HasMany
    {
        return $this->hasMany(Course::class, 'to_language_id');
    }
}
