<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Mirrors Duolingo's `/users/{id}/progress` response shape (language_data map).
 */
class UserProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalXp = $this->totalXp();
        $crowns = (int) $this->skillProgress()->sum('crowns');

        $level = max(1, intdiv($totalXp, 100) + 1);
        $pointsToNextLevel = $level * 100;
        $remaining = max(0, $pointsToNextLevel - $totalXp);

        $language = $this->activeCourse()?->toLanguage?->code ?? 'en';

        return [
            'language_data' => [
                $language => [
                    'level' => $level,
                    'points' => $totalXp,
                    'fluency' => min(100, intdiv($crowns * 100, max(1, $crowns + 5))),
                    'next_level' => $level + 1,
                    'points_to_next_level' => $remaining,
                    'progress_to_next_level' => $pointsToNextLevel > 0
                        ? (int) round((($pointsToNextLevel - $remaining) / $pointsToNextLevel) * 100)
                        : 0,
                    'crowns' => $crowns,
                    'streak' => (int) ($this->streak?->current ?? 0),
                    'xp_goal' => (int) config('duolingo.daily_goal'),
                    'unit' => 1,
                ],
            ],
        ];
    }
}
