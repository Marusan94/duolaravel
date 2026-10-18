<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    protected $fillable = ['key', 'name', 'description', 'type', 'price', 'payload', 'is_active'];

    protected $casts = ['payload' => 'array', 'is_active' => 'boolean'];

    public function userItems(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }
}
