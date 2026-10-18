<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ActivityFeed;
use App\Models\User;
use App\Models\UserAchievement;

class AchievementsService
{
    /**
     * Evaluate every achievement against the user's current stats.
     *
     * @return array<int, Achievement> newly awarded achievements
     */
    public function evaluate(User $user): array
    {
        $stats = [
            'first_lesson' => $user->userLessons()->where('status', 'completed')->count(),
            'streak_7' => (int) ($user->streak()->first()?->longest ?? 0),
            'xp_1000' => $user->totalXp(),
            'crowns_10' => (int) $user->skillProgress()->sum('crowns'),
        ];

        $awarded = [];

        foreach (Achievement::all() as $achievement) {
            $value = $stats[$achievement->key] ?? null;

            if ($value === null || $value < ($achievement->threshold ?? 1)) {
                continue;
            }

            $record = UserAchievement::firstOrCreate(
                ['user_id' => $user->id, 'achievement_id' => $achievement->id],
                ['achieved_at' => now()]
            );

            if ($record->wasRecentlyCreated) {
                $awarded[] = $achievement;
                ActivityFeed::create([
                    'user_id' => $user->id,
                    'type' => 'achievement',
                    'description' => 'Desbloqueó el logro "'.$achievement->name.'"',
                    'meta' => ['achievement' => $achievement->key],
                ]);
            }
        }

        return $awarded;
    }
}
