<?php

namespace Tests\Feature;

use App\Jobs\RegenerateHearts;
use App\Models\League;
use App\Models\User;
use App\Services\GamificationService;
use App\Services\LeagueService;
use Database\Seeders\DuolingoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueAndHeartsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DuolingoSeeder::class);
    }

    public function test_hearts_regenerate_when_due(): void
    {
        $user = User::factory()->create();
        $gamification = app(GamificationService::class);

        $heart = $gamification->loseHeart($user);
        $heart->forceFill(['next_regen_at' => now()->subMinute()])->save();

        RegenerateHearts::dispatchSync();

        $this->assertEquals(5, $user->fresh()->heart->current);
    }

    public function test_hearts_do_not_regenerate_before_due(): void
    {
        $user = User::factory()->create();
        app(GamificationService::class)->loseHeart($user);

        RegenerateHearts::dispatchSync();

        $this->assertEquals(4, $user->fresh()->heart->current);
    }

    public function test_close_week_snapshots_ranks_and_promotes(): void
    {
        $users = User::factory()->count(6)->create();
        $leagues = app(LeagueService::class);

        foreach ($users as $i => $user) {
            $leagues->ensureMembership($user, 1)->update(['weekly_xp' => (6 - $i) * 10]);
        }

        $result = $leagues->closeWeek(promote: 2, demote: 2);

        $this->assertCount(2, $result['promoted']);

        $topLeague = League::where('tier', 2)->first();
        $this->assertNotNull($topLeague);
        $this->assertTrue(
            $topLeague->members()->where('user_id', $users[0]->id)->exists(),
            'El líder de la liga debería ascender a la liga N2'
        );
        $this->assertEquals(1, $leagues->currentLeagueFor(1)->members()->where('user_id', $users[0]->id)->value('rank'));
    }

    public function test_progress_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/progress')->assertOk()->assertSee('Tu progreso');
    }
}
