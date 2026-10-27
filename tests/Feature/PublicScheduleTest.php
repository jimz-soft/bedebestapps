<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchTeam;
use App\Models\Team;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_schedule_shows_placeholder_when_empty(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('jadwal'))
            ->assertOk()
            ->assertSee('Jadwal')
            ->assertSee('Pertandingan')
            ->assertSee('Jadwal pertandingan belum diisi panitia.')
            ->assertDontSee('Halaman ini menyusul');
    }

    public function test_public_schedule_renders_duel_and_heat_cards_from_mockup(): void
    {
        $this->seed(DatabaseSeeder::class);

        $teams = Team::query()->orderBy('sort_order')->get();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $mukbang = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $live = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'live',
        ]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $teams[0]->id, 'sort_order' => 1, 'score_cache' => 2]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $teams[1]->id, 'sort_order' => 2, 'score_cache' => 1]);

        $upcoming = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:30:00',
            'location' => 'GOR',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $teams[2]->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $teams[3]->id, 'sort_order' => 2]);

        $heat = GameMatch::query()->create([
            'game_id' => $mukbang->id,
            'round_label' => 'Lomba bersama',
            'scheduled_at' => '2026-11-21 18:00:00',
            'location' => 'Aula utama',
            'status' => 'upcoming',
        ]);
        foreach ($teams as $index => $team) {
            MatchTeam::query()->create(['match_id' => $heat->id, 'team_id' => $team->id, 'sort_order' => $index + 1]);
        }

        $this->get(route('jadwal'))
            ->assertOk()
            ->assertSee('Sabtu, 21 November 2026')
            ->assertSee('ACHILLES')
            ->assertSee('ODYSSEUS')
            ->assertSee('Live')
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('16:30')
            ->assertSee('21 Nov · GOR')
            ->assertSee('HERCULES')
            ->assertSee('PERSEUS')
            ->assertSee('18:00')
            ->assertSee('MUKBANG')
            ->assertSee('AULA UTAMA')
            ->assertSee('Lomba bersama');

        $this->get(route('jadwal', ['game' => 'mukbang']))
            ->assertOk()
            ->assertSee('MUKBANG')
            ->assertDontSee('ACHILLES')
            ->assertDontSee('16:30');

        $this->get(route('jadwal', ['status' => 'live']))
            ->assertOk()
            ->assertSee('ACHILLES')
            ->assertDontSee('16:30')
            ->assertDontSee('MUKBANG');
    }

    public function test_schedule_icons_use_logo_not_group_banner(): void
    {
        $this->seed(DatabaseSeeder::class);

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $match = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:30:00',
            'location' => 'GOR',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $match->id, 'team_id' => $achilles->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $match->id, 'team_id' => $odysseus->id, 'sort_order' => 2]);

        $this->get(route('jadwal'))
            ->assertOk()
            ->assertSee('>ACH<', false)
            ->assertSee('>ODY<', false)
            ->assertDontSee('images/teams/achilles.jpg')
            ->assertDontSee('images/teams/odysseus.jpg');

        $achilles->update(['logo_path' => 'logos/teams/achilles.png']);

        $this->get(route('jadwal'))
            ->assertOk()
            ->assertSee('storage/logos/teams/achilles.png')
            ->assertDontSee('images/teams/achilles.jpg');
    }

    public function test_finished_match_marks_winning_team_with_a_star(): void
    {
        $this->seed(DatabaseSeeder::class);

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $match = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:30:00',
            'location' => 'GOR',
            'status' => 'selesai',
        ]);
        MatchTeam::query()->create([
            'match_id' => $match->id,
            'team_id' => $achilles->id,
            'sort_order' => 1,
            'score_cache' => 3,
            'result' => 'win',
        ]);
        MatchTeam::query()->create([
            'match_id' => $match->id,
            'team_id' => $odysseus->id,
            'sort_order' => 2,
            'score_cache' => 1,
            'result' => 'loss',
        ]);

        $html = $this->get(route('jadwal', ['status' => 'selesai']))
            ->assertOk()
            ->assertSee('Selesai')
            ->assertSee('ACHILLES')
            ->assertSee('ODYSSEUS')
            ->assertSee('Pemenang')
            ->assertSee('★')
            ->getContent();

        $this->assertSame(1, substr_count($html, 'aria-label="Pemenang"'));
    }
}
