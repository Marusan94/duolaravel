<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mirrors Duolingo's `/leaderboard` (active_leaderboard with rankings).
 */
class LeaderboardController extends Controller
{
    public function __invoke(Request $request, LeagueService $leagues): JsonResponse
    {
        $user = $request->filled('user_id')
            ? User::find($request->integer('user_id'))
            : $request->user();

        $members = $leagues->leaderboard($user);
        $league = $members->first()?->league;

        return response()->json([
            'active_leaderboard' => [
                'league_id' => $league?->id,
                'tier' => $league?->tier,
                'season' => $league?->season,
                'week_start' => $league?->week_start,
                'rankings' => $members->values()->map(fn ($member, $index) => [
                    'user_id' => (int) $member->user_id,
                    'display_name' => $member->user?->name,
                    'score' => (int) $member->weekly_xp,
                    'rank' => $index + 1,
                ])->all(),
            ],
        ]);
    }
}
