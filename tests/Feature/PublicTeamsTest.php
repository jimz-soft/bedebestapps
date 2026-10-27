<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\GameRoster;
use App\Models\MatchTeam;
use App\Models\Participant;
use App\Models\Team;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTeamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_teams_page_lists_groups_with_rank_and_points(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seedTeamCatalog();

        $this->get(route('tim'))
            ->assertOk()
            ->assertSee('EMPAT GRUP')
            ->assertSee('Achilles')
            ->assertSee('Odysseus')
            ->assertSee('Hercules')
            ->assertSee('Perseus')
            ->assertSee('Peringkat')
            ->assertSee('#1')
            ->assertSee('#2')
            ->assertSee('58 poin')
            ->assertSee('54 poin')
            ->assertSee('8 peserta')
            ->assertSee('2 kapten')
            ->assertSee('Games 46')
            ->assertSee('Personil 12')
            ->assertSee(route('tim.show', 'achilles'), false)
            ->assertDontSee('Halaman ini menyusul')
            ->assertDontSee('images/teams/achilles.jpg');
    }

    public function test_team_profile_shows_participants_roster_and_matches(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$achilles] = $this->seedTeamCatalog();

        $this->get(route('tim.show', $achilles))
            ->assertOk()
            ->assertSee('ACHILLES')
            ->assertSee('Peringkat')
            ->assertSee('#2')
            ->assertSee('Kapten: Andika Pratama · Sari Wulandari')
            ->assertSee('Daftar peserta')
            ->assertSee('Andika Pratama')
            ->assertSee('Budi Santoso')
            ->assertSee('Roster per game')
            ->assertSee('Mini Soccer')
            ->assertSee('Voli')
            ->assertDontSee('Mukbang roster')
            ->assertSee('Andika Pratama')
            ->assertSee('Live vs Odysseus')
            ->assertSee('2 : 1')
            ->assertSee('Voli vs Hercules')
            ->assertSee('Upcoming')
            ->assertSee('Mukbang Lomba bersama')
            ->assertSee('4 grup')
            ->assertSee('Banner Achilles')
            ->assertSee('images/teams/achilles.jpg')
            ->assertDontSee('Halaman ini menyusul');

        $achilles->update(['banner_path' => 'banners/teams/achilles.jpg']);

        $this->get(route('tim.show', $achilles))
            ->assertOk()
            ->assertSee('storage/banners/teams/achilles.jpg')
            ->assertDontSee('images/teams/achilles.jpg');

        $html = $this->get(route('tim.show', $achilles))->getContent();
        $rosterStart = strpos($html, 'Roster per game');
        $matchStart = strpos($html, 'Pertandingan Achilles');
        $this->assertNotFalse($rosterStart);
        $this->assertNotFalse($matchStart);
        $rosterHtml = substr($html, $rosterStart, $matchStart - $rosterStart);
        $this->assertStringContainsString('Mini Soccer', $rosterHtml);
        $this->assertStringNotContainsString('Mukbang', $rosterHtml);
    }

    public function test_home_group_cards_link_to_team_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('tim.show', 'achilles'), false);
    }

    public function test_unknown_team_slug_is_not_found(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/tim/tidak-ada')->assertNotFound();
    }

    /**
     * @return array{0: Team, 1: Team, 2: Team}
     */
    private function seedTeamCatalog(): array
    {
        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $hercules = Team::query()->where('slug', 'hercules')->firstOrFail();
        $perseus = Team::query()->where('slug', 'perseus')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $voli = Game::query()->where('slug', 'voli')->firstOrFail();
        $mukbang = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $andika = Participant::query()->create([
            'team_id' => $achilles->id,
            'name' => 'Andika Pratama',
            'is_captain' => true,
            'is_player' => true,
            'is_personil' => true,
        ]);
        $sari = Participant::query()->create([
            'team_id' => $achilles->id,
            'name' => 'Sari Wulandari',
            'is_captain' => true,
            'is_player' => true,
            'is_personil' => true,
        ]);
        $budi = Participant::query()->create([
            'team_id' => $achilles->id,
            'name' => 'Budi Santoso',
            'is_captain' => false,
            'is_player' => true,
            'is_personil' => true,
        ]);

        foreach (['Citra', 'Dina', 'Eko', 'Fajar', 'Gita'] as $name) {
            Participant::query()->create([
                'team_id' => $achilles->id,
                'name' => $name,
                'is_captain' => false,
                'is_player' => true,
                'is_personil' => true,
            ]);
        }

        GameRoster::query()->create([
            'game_id' => $soccer->id,
            'team_id' => $achilles->id,
            'participant_id' => $andika->id,
        ]);
        GameRoster::query()->create([
            'game_id' => $soccer->id,
            'team_id' => $achilles->id,
            'participant_id' => $budi->id,
        ]);
        GameRoster::query()->create([
            'game_id' => $voli->id,
            'team_id' => $achilles->id,
            'participant_id' => $sari->id,
        ]);

        $finished = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Penyisihan',
            'scheduled_at' => '2026-11-20 09:00:00',
            'status' => 'selesai',
        ]);
        MatchTeam::query()->create([
            'match_id' => $finished->id,
            'team_id' => $hercules->id,
            'sort_order' => 1,
            'points_awarded' => 46,
            'poin_personil' => 12,
            'bonus_pemenang' => 0,
            'score_cache' => 3,
        ]);
        MatchTeam::query()->create([
            'match_id' => $finished->id,
            'team_id' => $achilles->id,
            'sort_order' => 2,
            'points_awarded' => 42,
            'poin_personil' => 12,
            'bonus_pemenang' => 0,
            'score_cache' => 1,
        ]);

        $live = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'live',
        ]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $achilles->id, 'sort_order' => 1, 'score_cache' => 2]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $odysseus->id, 'sort_order' => 2, 'score_cache' => 1]);

        $upcoming = GameMatch::query()->create([
            'game_id' => $voli->id,
            'round_label' => 'Round robin',
            'scheduled_at' => '2026-11-21 10:00:00',
            'location' => 'GOR',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $achilles->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $hercules->id, 'sort_order' => 2]);

        $heat = GameMatch::query()->create([
            'game_id' => $mukbang->id,
            'round_label' => 'Lomba bersama',
            'scheduled_at' => '2026-11-21 18:00:00',
            'location' => 'Aula utama',
            'status' => 'upcoming',
        ]);
        foreach ([$achilles, $odysseus, $hercules, $perseus] as $index => $team) {
            MatchTeam::query()->create(['match_id' => $heat->id, 'team_id' => $team->id, 'sort_order' => $index + 1]);
        }

        return [$achilles, $odysseus, $hercules];
    }
}
