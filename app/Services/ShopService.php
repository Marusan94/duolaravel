<?php

namespace App\Services;

use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;

class ShopService
{
    public function __construct(private GamificationService $gamification) {}

    public function purchase(User $user, ShopItem $item): bool
    {
        if (! $this->gamification->spendGems($user, $item->price, 'shop:'.$item->key)) {
            return false;
        }

        $owned = UserItem::firstOrCreate(
            ['user_id' => $user->id, 'shop_item_id' => $item->id],
            ['quantity' => 0, 'active' => false]
        );
        $owned->increment('quantity');

        return true;
    }

    public function apply(User $user, UserItem $userItem): void
    {
        match ($userItem->item->type) {
            'heart_refill' => $this->gamification->refillHearts($user),
            'streak_freeze' => $user->streak()->first()?->increment('freezes'),
            default => null,
        };

        $userItem->decrement('quantity');
    }
}
