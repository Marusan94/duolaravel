<?php

namespace App\Livewire;

use App\Models\UserLesson;
use App\Models\UserSkillProgress;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Aprender')]
class LearningPath extends Component
{
    public function render()
    {
        $user = auth()->user();
        $course = $user->activeCourse();

        $completedLessons = UserLesson::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->all();

        $skillCrowns = UserSkillProgress::where('user_id', $user->id)
            ->pluck('crowns', 'skill_id');

        $units = $course ? $course->units()->with('skills.lessons')->get() : collect();

        $palette = ['green', 'blue', 'purple', 'yellow', 'orange'];

        $path = $units->values()->map(function ($unit, $ui) use ($completedLessons, $skillCrowns, $palette) {
            return [
                'id' => $unit->id,
                'title' => $unit->title,
                'description' => $unit->description,
                'guide' => $unit->guide,
                'color' => $palette[$ui % count($palette)],
                'skills' => $unit->skills->values()->map(function ($skill) use ($completedLessons, $skillCrowns) {
                    $lessons = $skill->lessons->values();
                    $total = $lessons->count();
                    $done = $lessons->filter(fn ($lesson) => in_array($lesson->id, $completedLessons, true))->count();
                    $next = $lessons->first(fn ($lesson) => ! in_array($lesson->id, $completedLessons, true)) ?? $lessons->first();

                    return [
                        'id' => $skill->id,
                        'title' => $skill->title,
                        'icon' => $skill->icon,
                        'total' => $total,
                        'done' => $done,
                        'pct' => $total ? (int) round($done / $total * 100) : 0,
                        'next_lesson' => $next,
                        'crowns' => (int) ($skillCrowns[$skill->id] ?? 0),
                    ];
                }),
            ];
        });

        // Marca el primer skill no completado como "activo"; los siguientes quedan bloqueados.
        $activeFound = false;
        $path = $path->map(function ($unit) use (&$activeFound) {
            $unit['skills'] = $unit['skills']->map(function ($skill) use (&$activeFound) {
                if ($skill['pct'] >= 100) {
                    $skill['state'] = 'done';
                } elseif (! $activeFound) {
                    $skill['state'] = 'active';
                    $activeFound = true;
                } else {
                    $skill['state'] = 'locked';
                }

                return $skill;
            });

            return $unit;
        });

        return view('livewire.learning-path', [
            'course' => $course,
            'path' => $path,
            'totalXp' => $user->totalXp(),
        ]);
    }
}
