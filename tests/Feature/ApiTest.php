<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DuolingoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DuolingoSeeder::class);
    }

    public function test_config_endpoint_returns_feature_flags(): void
    {
        $this->getJson('/api/v1/config')
            ->assertOk()
            ->assertJsonStructure(['ageRestrictionLimit', 'ipCountry', 'speechHost', 'featureFlags'])
            ->assertJsonPath('ageRestrictionLimit', 13)
            ->assertJsonPath('speechHost', 'speech.duolingo.com');
    }

    public function test_courses_endpoint_returns_flat_list_in_duolingo_shape(): void
    {
        $json = $this->getJson('/api/v1/courses')->assertOk()->json();

        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
        $this->assertArrayNotHasKey('data', $json);
        $this->assertArrayHasKey('learning_language', $json[0]);
        $this->assertArrayHasKey('from_language', $json[0]);
        $this->assertArrayHasKey('num_learners', $json[0]);
        $this->assertArrayHasKey('phase', $json[0]);
    }

    public function test_user_progress_endpoint_returns_language_data(): void
    {
        $user = User::factory()->create();

        $json = $this->getJson("/api/v1/users/{$user->id}/progress")->assertOk()->json();

        $this->assertArrayHasKey('language_data', $json);
        $this->assertIsArray($json['language_data']);
        $language = array_key_first($json['language_data']);
        $this->assertArrayHasKey('level', $json['language_data'][$language]);
        $this->assertArrayHasKey('points', $json['language_data'][$language]);
    }

    public function test_leaderboard_endpoint_returns_active_leaderboard(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/v1/leaderboard?user_id={$user->id}")
            ->assertOk()
            ->assertJsonStructure(['active_leaderboard' => ['tier', 'week_start', 'rankings']]);
    }
}
