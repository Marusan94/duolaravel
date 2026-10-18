<?php

namespace App\Services;

use App\Models\GemTransaction;
use App\Models\GemsWallet;
use App\Models\Heart;
use App\Models\Streak;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\XpEvent;
use Illuminate\Database\Eloquent\Model;

class GamificationService
{
    public function ensureProfile(User $user): void
    {
        $user->streak()->firstOrCreate([], ['current' => 0, 'longest' => 0, 'freezes' => 0]);
        $user->heart()->firstOrCreate([], ['current' => 5, 'max' => 5]);
        $user->gemsWallet()->firstOrCreate([], ['balance' => 0]);
    }

    public function awardXp(User $user, int $amount, string $source, ?Model $reference = null): XpEvent
    {
        return XpEvent::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'source' => $source,
            'occurred_on' => now()->toDateString(),
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
        ]);
    }

    public function touchStreak(User $user): Streak
    {
        $streak = $user->streak()->firstOrCreate([], ['current' => 0, 'longest' => 0, 'freezes' => 0]);
        $today = now()->toDateString();

        if ($streak->last_activity_on?->toDateString() === $today) {
            return $streak;
        }

        $yesterday = now()->subDay()->toDateString();

        if ($streak->last_activity_on?->toDateString() === $yesterday) {
            $streak->current++;
        } else {
            $streak->current = 1;
        }

        $streak->longest = max($streak->longest, $streak->current);
        $streak->last_activity_on = $today;
        $streak->save();

        return $streak;
    }

    public function loseHeart(User $user): Heart
    {
        $heart = $user->heart()->firstOrCreate([], ['current' => 5, 'max' => 5]);
        $heart->current = max(0, $heart->current - 1);

        if ($heart->current < $heart->max && ! $heart->next_regen_at) {
            $heart->next_regen_at = now()->addMinutes(30);
        }

        $heart->save();

        return $heart;
    }

    public function regenerateHearts(User $user): Heart
    {
        $heart = $user->heart()->firstOrCreate([], ['current' => 5, 'max' => 5]);

        if ($heart->current >= $heart->max) {
            $heart->next_regen_at = null;
            $heart->save();

            return $heart;
        }

        if ($heart->next_regen_at && $heart->next_regen_at->isPast()) {
            $heart->current = min($heart->max, $heart->current + 1);
            $heart->next_regen_at = $heart->current < $heart->max ? now()->addMinutes(30) : null;
            $heart->save();
        }

        return $heart;
    }

    public function refillHearts(User $user): Heart
    {
        $heart = $user->heart()->firstOrCreate([], ['current' => 5, 'max' => 5]);
        $heart->forceFill(['current' => $heart->max, 'next_regen_at' => null])->save();

        return $heart;
    }

    public function awardGems(User $user, int $amount, string $reason): GemsWallet
    {
        $wallet = $user->gemsWallet()->firstOrCreate([], ['balance' => 0]);
        $wallet->increment('balance', $amount);
        GemTransaction::create(['user_id' => $user->id, 'amount' => $amount, 'reason' => $reason]);

        return $wallet->refresh();
    }

    public function spendGems(User $user, int $amount, string $reason): bool
    {
        $wallet = $user->gemsWallet()->firstOrCreate([], ['balance' => 0]);

        if ($wallet->balance < $amount) {
            return false;
        }

        $wallet->decrement('balance', $amount);
        GemTransaction::create(['user_id' => $user->id, 'amount' => -$amount, 'reason' => $reason]);

        return true;
    }

    public function notify(User $user, string $type, string $title, ?string $body = null): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }
}
