<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchTeam;
use App\Models\Team;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_lists_groups_and_banners(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('leaderboard'))
            ->assertOk()
            ->assertSee('LEADERBOARD')
            ->assertSee('Juara umum')
            ->assertSee('Achilles')
            ->assertSee('Odysseus')
            ->assertSee('Hercules')
            ->assertSee('Perseus')
            ->assertSee('Banner Achilles')
            ->assertSee('images/teams/achilles.jpg')
            ->assertSee('Banner Hercules')
            ->assertSee(route('tim.show', 'achilles'), false)
            ->assertDontSee('Halaman ini menyusul');
    }

    public function test_leaderboard_ranks_by_finished_match_points(): void
    {
        $this->seed(DatabaseSeeder::class);

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $hercules = Team::query()->where('slug', 'hercules')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $match = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Penyisihan',
            'scheduled_at' => '2026-11-20 09:00:00',
            'status' => 'selesai',
        ]);
        MatchTeam::query()->create([
            'match_id' => $match->id,
            'team_id' => $hercules->id,
            'sort_order' => 1,
            'points_awarded' => 46,
            'poin_personil' => 12,
            'bonus_pemenang' => 0,
            'result' => 'win',
        ]);
        MatchTeam::query()->create([
            'match_id' => $match->id,
            'team_id' => $achilles->id,
            'sort_order' => 2,
            'points_awarded' => 42,
            'poin_personil' => 12,
            'bonus_pemenang' => 0,
            'result' => 'loss',
        ]);

        $html = $this->get(route('leaderboard'))
            ->assertOk()
            ->assertSee('Hercules')
            ->assertSee('Achilles')
            ->assertSee('58')
            ->assertSee('54')
            ->assertSee('46')
            ->assertSee('42')
            ->assertSee('12')
            ->getContent();

        $this->assertLessThan(
            strpos($html, 'Hercules'),
            strpos($html, '#1'),
        );
        $herculesPos = strpos($html, '>Hercules<');
        $achillesPos = strpos($html, '>Achilles<');
        $this->assertNotFalse($herculesPos);
        $this->assertNotFalse($achillesPos);
        $this->assertLessThan($achillesPos, $herculesPos);
    }

    public function test_live_points_do_not_enter_leaderboard_until_finished(): void
    {
        $this->seed(DatabaseSeeder::class);

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $live = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'status' => 'live',
        ]);
        MatchTeam::query()->create([
            'match_id' => $live->id,
            'team_id' => $achilles->id,
            'sort_order' => 1,
            'points_awarded' => 20,
            'poin_personil' => 5,
        ]);
        MatchTeam::query()->create([
            'match_id' => $live->id,
            'team_id' => $odysseus->id,
            'sort_order' => 2,
            'points_awarded' => 10,
            'poin_personil' => 3,
        ]);

        $this->get(route('leaderboard'))
            ->assertOk()
            ->assertSee('0 PTS')
            ->assertDontSee('>20</span>', false)
            ->assertDontSee('>25</span>', false);
    }
}
