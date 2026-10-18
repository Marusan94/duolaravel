<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    protected $fillable = ['tier', 'season', 'week_start'];

    public function members(): HasMany
    {
        return $this->hasMany(LeagueMember::class);
    }
}
