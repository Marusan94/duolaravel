<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use App\Services\LessonRunner;
use Database\Seeders\DuolingoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DuolingoSeeder::class);
    }

    public function test_wrong_answer_loses_a_heart(): void
    {
        $user = User::factory()->create();
        $lesson = Lesson::with('exercises')->first();
        $exercise = $lesson->exercises()->where('type', 'translate')->first();

        $runner = app(LessonRunner::class);
        $runner->start($user, $lesson);

        $result = $runner->answer($user, $exercise, 'zzz');

        $this->assertFalse($result['correct']);
        $this->assertEquals(4, $user->fresh()->heart->current);
    }

    public function test_completing_a_lesson_awards_xp_streak_and_crowns(): void
    {
        $user = User::factory()->create();
        $lesson = Lesson::first();

        app(LessonRunner::class)->complete($user, $lesson);

        $user->refresh();
        $this->assertEquals(10, $user->totalXp());
        $this->assertEquals(1, $user->streak->current);
        $this->assertEquals(2, $user->gemsWallet->balance);
        $this->assertEquals(1, (int) $user->skillProgress()->sum('crowns'));
        $this->assertDatabaseHas('user_lessons', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'status' => 'completed',
        ]);
    }

    public function test_all_exercise_types_accept_their_correct_answers(): void
    {
        $user = User::factory()->create();
        $lesson = Lesson::with('exercises')->first();
        $runner = app(LessonRunner::class);

        foreach ($lesson->exercises as $exercise) {
            $answer = match ($exercise->type) {
                'select_translation', 'select_image', 'listen_select' => $exercise->options()->where('is_correct', true)->value('id'),
                'order_words', 'order_tokens' => $exercise->payload['answer'],
                'match_pairs' => $exercise->payload['pairs'],
                default => $exercise->payload['answers'][0] ?? '',
            };

            $this->assertTrue(
                $runner->answer($user, $exercise, $answer)['correct'],
                "El tipo {$exercise->type} no aceptó su respuesta correcta"
            );
        }
    }

    public function test_first_lesson_achievement_is_unlocked(): void
    {
        $user = User::factory()->create();
        $lesson = Lesson::first();

        $unlocked = app(LessonRunner::class)->complete($user, $lesson);

        $this->assertNotEmpty($unlocked);
        $this->assertDatabaseHas('user_achievements', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'type' => 'achievement']);
    }

    public function test_learn_page_requires_authentication(): void
    {
        $this->get('/learn')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_see_learning_path(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/learn')->assertOk();
    }
}
