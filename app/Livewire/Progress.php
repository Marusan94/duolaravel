<?php

namespace App\Livewire;

use App\Models\GemTransaction;
use App\Models\UserAchievement;
use App\Models\UserLesson;
use App\Models\UserSkillProgress;
use App\Services\LeagueService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Progreso')]
class Progress extends Component
{
    public function render()
    {
        $user = auth()->user();
        $member = app(LeagueService::class)->ensureMembership($user);

        $course = $user->activeCourse();

        $completedLessons = UserLesson::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->all();

        $palette = ['green', 'blue', 'purple', 'yellow', 'orange'];

        $unitProgress = ($course ? $course->units()->with('skills.lessons')->get() : collect())
            ->values()
            ->map(function ($unit, $ui) use ($completedLessons, $palette) {
                $lessons = $unit->skills->flatMap->lessons->unique('id')->values();
                $total = $lessons->count();
                $done = $lessons->filter(fn ($lesson) => in_array($lesson->id, $completedLessons, true))->count();

                return [
                    'id' => $unit->id,
                    'title' => $unit->title,
                    'color' => $palette[$ui % count($palette)],
                    'total' => $total,
                    'done' => $done,
                    'pct' => $total ? (int) round($done / $total * 100) : 0,
                ];
            });

        return view('livewire.progress', [
            'totalXp' => $user->totalXp(),
            'weeklyXp' => $user->weeklyXp(),
            'streak' => $user->streak()->first(),
            'crowns' => (int) UserSkillProgress::where('user_id', $user->id)->sum('crowns'),
            'lessonsCompleted' => UserLesson::where('user_id', $user->id)->where('status', 'completed')->count(),
            'achievementsCount' => UserAchievement::where('user_id', $user->id)->count(),
            'gemsEarned' => (int) GemTransaction::where('user_id', $user->id)->where('amount', '>', 0)->sum('amount'),
            'leagueTier' => $member->league->tier,
            'leagueRank' => $member->rank,
            'unitProgress' => $unitProgress,
        ]);
    }
}
