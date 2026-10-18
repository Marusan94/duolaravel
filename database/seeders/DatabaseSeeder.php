<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use App\Services\GamificationService;
use App\Services\LeagueService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DuolingoSeeder::class);
        $this->call(ExamCurriculumSeeder::class);

        $gamification = app(GamificationService::class);
        $leagues = app(LeagueService::class);

        $users = [
            ['name' => 'Santiago', 'email' => 'maru9419@hotmail.com'],
            ['name' => 'Demo', 'email' => 'demo@example.com'],
            ['name' => 'Ana', 'email' => 'ana@example.com'],
            ['name' => 'Luis', 'email' => 'luis@example.com'],
            ['name' => 'Marta', 'email' => 'marta@example.com'],
        ];

        $course = Course::first();

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => 'password']
            );

            $gamification->ensureProfile($user);
            $leagues->ensureMembership($user);

            if ($course) {
                $user->userCourses()->firstOrCreate(
                    ['course_id' => $course->id],
                    ['is_active' => true, 'started_at' => now()]
                );
            }
        }
    }
}
