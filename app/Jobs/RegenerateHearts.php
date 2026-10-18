<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RegenerateHearts implements ShouldQueue
{
    use Queueable;

    public function handle(GamificationService $gamification): void
    {
        User::query()
            ->whereHas('heart', function ($query) {
                $query->whereColumn('current', '<', 'max')
                    ->whereNotNull('next_regen_at')
                    ->where('next_regen_at', '<=', now());
            })
            ->with('heart')
            ->chunkById(100, function ($users) use ($gamification) {
                foreach ($users as $user) {
                    $gamification->regenerateHearts($user);
                }
            });
    }
}
