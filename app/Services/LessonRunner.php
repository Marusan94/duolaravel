<?php

namespace App\Services;

use App\Models\ActivityFeed;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserLesson;
use App\Models\UserSkillProgress;

class LessonRunner
{
    public function __construct(
        private ExerciseEngine $engine,
        private GamificationService $gamification,
        private LeagueService $leagues,
        private AchievementsService $achievements,
    ) {}

    public function start(User $user, Lesson $lesson): UserLesson
    {
        $userLesson = UserLesson::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['status' => 'in_progress']
        );

        if (in_array($userLesson->status, ['locked', 'available'], true)) {
            $userLesson->update(['status' => 'in_progress']);
        }

        return $userLesson;
    }

    /**
     * @return array{correct: bool, hearts: int|null}
     */
    public function answer(User $user, Exercise $exercise, mixed $answer): array
    {
        $correct = $this->engine->validate($exercise, $answer);

        if ($correct) {
            $this->gamification->awardXp($user, 5, 'exercise', $exercise);
            $this->leagues->addWeeklyXp($user, 5);
        } else {
            $this->gamification->loseHeart($user);
        }

        return [
            'correct' => $correct,
            'hearts' => $user->heart()->first()?->current,
        ];
    }

    /**
     * @return array<int, \App\Models\Achievement> achievements unlocked by this completion
     */
    public function complete(User $user, Lesson $lesson, int $score = 100): array
    {
        $userLesson = UserLesson::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['status' => 'in_progress']
        );
        $userLesson->update(['status' => 'completed', 'score' => $score, 'completed_at' => now()]);

        $this->gamification->awardXp($user, $lesson->xp_reward, 'lesson', $lesson);
        $this->leagues->addWeeklyXp($user, $lesson->xp_reward);
        $this->gamification->touchStreak($user);
        $this->gamification->awardGems($user, 2, 'lesson_complete');

        $progress = UserSkillProgress::firstOrCreate(
            ['user_id' => $user->id, 'skill_id' => $lesson->skill_id],
            ['crowns' => 0, 'level' => 0]
        );
        $progress->increment('crowns');
        $progress->update(['last_practiced_at' => now(), 'level' => $progress->crowns]);

        ActivityFeed::create([
            'user_id' => $user->id,
            'type' => 'lesson_complete',
            'description' => 'Completó "'.$lesson->title.'"',
            'meta' => ['lesson_id' => $lesson->id, 'score' => $score],
        ]);

        $unlocked = $this->achievements->evaluate($user);

        foreach ($unlocked as $achievement) {
            $this->gamification->notify($user, 'achievement', '¡Logro desbloqueado!', $achievement->name);
        }

        return $unlocked;
    }
}
