<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LeagueService
{
    public function currentLeagueFor(int $tier = 1): League
    {
        return League::firstOrCreate([
            'tier' => $tier,
            'season' => now()->format('Y'),
            'week_start' => now()->startOfWeek()->toDateString(),
        ]);
    }

    public function ensureMembership(User $user, int $tier = 1): LeagueMember
    {
        $league = $this->currentLeagueFor($tier);

        return LeagueMember::firstOrCreate(
            ['league_id' => $league->id, 'user_id' => $user->id],
            ['weekly_xp' => 0]
        );
    }

    public function addWeeklyXp(User $user, int $amount): void
    {
        $this->ensureMembership($user)->increment('weekly_xp', $amount);
    }

    public function leaderboard(?User $user = null): Collection
    {
        $member = $user ? $this->ensureMembership($user) : null;
        $league = $member?->league ?? $this->currentLeagueFor();

        return $league->members()->with('user')->orderByDesc('weekly_xp')->get();
    }

    public function snapshotWeek(League $league): void
    {
        $league->members()
            ->orderByDesc('weekly_xp')
            ->get()
            ->each(function (LeagueMember $member, int $index) {
                $member->update(['rank' => $index + 1]);
            });
    }

    /**
     * Snapshot the current week and move top/bottom members to adjacent tiers.
     *
     * @return array{promoted: array<int, int>, demoted: array<int, int>}
     */
    public function closeWeek(int $promote = 3, int $demote = 3): array
    {
        $league = $this->currentLeagueFor();
        $this->snapshotWeek($league);

        $members = $league->members()->with('user')->orderByDesc('weekly_xp')->get();
        $total = $members->count();

        $promoted = [];
        $demoted = [];

        foreach ($members as $index => $member) {
            if ($index < $promote && $total > $promote) {
                $this->ensureMembership($member->user, $league->tier + 1);
                $promoted[] = (int) $member->user_id;
            } elseif ($index >= $total - $demote && $index >= $promote && $league->tier > 1) {
                $this->ensureMembership($member->user, max(1, $league->tier - 1));
                $demoted[] = (int) $member->user_id;
            }
        }

        return ['promoted' => $promoted, 'demoted' => $demoted];
    }
}
