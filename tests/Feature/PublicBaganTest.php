<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchPeriod;
use App\Models\MatchPeriodScore;
use App\Models\MatchTeam;
use App\Models\Team;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBaganTest extends TestCase
{
    use RefreshDatabase;

    public function test_bagan_page_shows_tabs_and_empty_states(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('bagan'))
            ->assertOk()
            ->assertSee('BAGAN')
            ->assertSee('Mini Soccer')
            ->assertSee('Voli')
            ->assertSee('Mukbang')
            ->assertSee('sistem gugur')
            ->assertSee('round robin')
            ->assertSee('lomba bersama')
            ->assertSee('Bagan gugur tampil setelah jadwal diisi panitia.')
            ->assertSee('Belum ada pertandingan round robin.')
            ->assertSee('Belum ada sesi lomba bersama.')
            ->assertDontSee('Halaman ini menyusul');
    }

    public function test_knockout_tree_and_klasemen_follow_finished_points(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$achilles, $odysseus, $hercules, $perseus] = $this->teams();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $live = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'live',
        ]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $achilles->id, 'sort_order' => 1, 'score_cache' => 2]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $odysseus->id, 'sort_order' => 2, 'score_cache' => 1]);

        $semi = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 16:30:00',
            'location' => 'GOR',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $semi->id, 'team_id' => $hercules->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $semi->id, 'team_id' => $perseus->id, 'sort_order' => 2]);

        $final = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 18:00:00',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $final->id, 'team_id' => $achilles->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $final->id, 'team_id' => $hercules->id, 'sort_order' => 2]);

        $done = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Penyisihan',
            'scheduled_at' => '2026-11-20 09:00:00',
            'status' => 'selesai',
        ]);
        MatchTeam::query()->create([
            'match_id' => $done->id,
            'team_id' => $hercules->id,
            'sort_order' => 1,
            'points_awarded' => 10,
            'poin_personil' => 4,
            'result' => 'win',
            'score_cache' => 3,
        ]);
        MatchTeam::query()->create([
            'match_id' => $done->id,
            'team_id' => $perseus->id,
            'sort_order' => 2,
            'points_awarded' => 2,
            'poin_personil' => 1,
            'result' => 'loss',
            'score_cache' => 1,
        ]);

        $html = $this->get(route('bagan'))
            ->assertOk()
            ->assertSee('Mini Soccer — sistem gugur')
            ->assertSee('Semifinal')
            ->assertSee('Final')
            ->assertSee('Achilles')
            ->assertSee('Odysseus')
            ->assertSee('Hercules')
            ->assertSee('Perseus')
            ->assertSee('Live')
            ->assertSee('16:30 · GOR')
            ->assertSee('Klasemen Mini Soccer')
            ->assertSee('1 live')
            ->assertSee('Pemenang')
            ->assertSee('★')
            ->getContent();

        $this->assertStringContainsString('id="panel-mini-soccer" class="bagan-panel active"', $html);
    }

    public function test_round_robin_shows_matrix_sets_and_match_list(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$achilles, $odysseus, $hercules] = $this->teams();
        $voli = Game::query()->where('slug', 'voli')->firstOrFail();

        $done = GameMatch::query()->create([
            'game_id' => $voli->id,
            'round_label' => 'Round robin',
            'scheduled_at' => '2026-11-21 08:00:00',
            'status' => 'selesai',
        ]);
        MatchTeam::query()->create([
            'match_id' => $done->id,
            'team_id' => $hercules->id,
            'sort_order' => 1,
            'points_awarded' => 16,
            'poin_personil' => 8,
            'result' => 'win',
            'score_cache' => 2,
        ]);
        MatchTeam::query()->create([
            'match_id' => $done->id,
            'team_id' => $achilles->id,
            'sort_order' => 2,
            'points_awarded' => 4,
            'poin_personil' => 3,
            'result' => 'loss',
            'score_cache' => 0,
        ]);
        $setOne = MatchPeriod::query()->create(['match_id' => $done->id, 'period_no' => 1, 'status' => 'done']);
        MatchPeriodScore::query()->create(['match_period_id' => $setOne->id, 'team_id' => $hercules->id, 'score' => 25]);
        MatchPeriodScore::query()->create(['match_period_id' => $setOne->id, 'team_id' => $achilles->id, 'score' => 18]);
        $setTwo = MatchPeriod::query()->create(['match_id' => $done->id, 'period_no' => 2, 'status' => 'done']);
        MatchPeriodScore::query()->create(['match_period_id' => $setTwo->id, 'team_id' => $hercules->id, 'score' => 25]);
        MatchPeriodScore::query()->create(['match_period_id' => $setTwo->id, 'team_id' => $achilles->id, 'score' => 20]);

        $upcoming = GameMatch::query()->create([
            'game_id' => $voli->id,
            'round_label' => 'Round robin',
            'scheduled_at' => '2026-11-21 16:30:00',
            'location' => 'GOR',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $odysseus->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $hercules->id, 'sort_order' => 2]);

        $this->get(route('bagan', ['game' => 'voli']))
            ->assertOk()
            ->assertSee('Voli — round robin')
            ->assertSee('Klasemen Voli')
            ->assertSee('Matriks hasil')
            ->assertSee('2–0')
            ->assertSee('0–2')
            ->assertSee('21 Nov')
            ->assertSee('Hercules')
            ->assertSee('Achilles')
            ->assertSee('25-18, 25-20')
            ->assertSee('Selesai')
            ->assertSee('16:30 · GOR');
    }

    public function test_heat_session_ranks_groups_by_session_points(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$achilles, $odysseus, $hercules, $perseus] = $this->teams();
        $mukbang = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $heat = GameMatch::query()->create([
            'game_id' => $mukbang->id,
            'round_label' => 'Sesi Final Mukbang',
            'scheduled_at' => '2026-11-21 18:00:00',
            'location' => 'Aula utama',
            'status' => 'selesai',
        ]);
        MatchTeam::query()->create(['match_id' => $heat->id, 'team_id' => $achilles->id, 'sort_order' => 1, 'points_awarded' => 10, 'poin_personil' => 9]);
        MatchTeam::query()->create(['match_id' => $heat->id, 'team_id' => $odysseus->id, 'sort_order' => 2, 'points_awarded' => 8, 'poin_personil' => 8]);
        MatchTeam::query()->create(['match_id' => $heat->id, 'team_id' => $hercules->id, 'sort_order' => 3, 'points_awarded' => 12, 'poin_personil' => 11]);
        MatchTeam::query()->create(['match_id' => $heat->id, 'team_id' => $perseus->id, 'sort_order' => 4, 'points_awarded' => 6, 'poin_personil' => 6]);

        $this->get(route('bagan', ['game' => 'mukbang']))
            ->assertOk()
            ->assertSee('Mukbang — lomba bersama')
            ->assertSee('Sesi Final Mukbang')
            ->assertSee('Aula utama')
            ->assertSee('roster off')
            ->assertSee('Hercules')
            ->assertSee('23')
            ->assertSee('Lihat jadwal');
    }

    public function test_inactive_games_are_hidden_from_bagan_tabs(): void
    {
        $this->seed(DatabaseSeeder::class);
        Game::query()->where('slug', 'voli')->update(['is_active' => false]);

        $this->get(route('bagan'))
            ->assertOk()
            ->assertSee('Mini Soccer')
            ->assertSee('Mukbang')
            ->assertDontSeeText('Voli');
    }

    /**
     * @return array{0: Team, 1: Team, 2: Team, 3: Team}
     */
    private function teams(): array
    {
        return [
            Team::query()->where('slug', 'achilles')->firstOrFail(),
            Team::query()->where('slug', 'odysseus')->firstOrFail(),
            Team::query()->where('slug', 'hercules')->firstOrFail(),
            Team::query()->where('slug', 'perseus')->firstOrFail(),
        ];
    }
}
