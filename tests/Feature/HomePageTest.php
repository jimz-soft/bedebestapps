<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchTeam;
use App\Models\Team;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_event_and_teams(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('BedeBest')
            ->assertSee('Achilles')
            ->assertSee('Odysseus')
            ->assertSee('Hercules')
            ->assertSee('Perseus')
            ->assertSee('Mini Soccer')
            ->assertSee('Jadwal pertandingan menyusul.')
            ->assertDontSee('Empat grup: Achilles')
            ->assertDontSee('roster on')
            ->assertDontSee('roster off');

        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_home_countdown_targets_nearest_upcoming_match(): void
    {
        $this->seed(DatabaseSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00', 'Asia/Jakarta'));

        $hercules = Team::query()->where('slug', 'hercules')->firstOrFail();
        $perseus = Team::query()->where('slug', 'perseus')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $voli = Game::query()->where('slug', 'voli')->firstOrFail();

        $next = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:30:00',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $next->id, 'team_id' => $hercules->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $next->id, 'team_id' => $perseus->id, 'sort_order' => 2]);

        $later = GameMatch::query()->create([
            'game_id' => $voli->id,
            'round_label' => 'Babak 1',
            'scheduled_at' => '2026-11-22 08:00:00',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $later->id, 'team_id' => $hercules->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $later->id, 'team_id' => $perseus->id, 'sort_order' => 2]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<p id="countdown-days" class="font-gaming text-3xl text-brand-cyan">01</p>', false)
            ->assertSee('<p id="countdown-hours" class="font-gaming text-3xl text-brand-cyan">06</p>', false)
            ->assertSee('<p id="countdown-minutes" class="font-gaming text-3xl text-brand-cyan">30</p>', false)
            ->assertSee('<p id="countdown-seconds" class="font-gaming text-3xl text-brand-cyan">00</p>', false)
            ->assertSee('Detik')
            ->assertSee('Countdown menuju pertandingan berikutnya.')
            ->assertSee('Menuju · Mini Soccer · Final')
            ->assertDontSee('Menuju · Voli')
            ->assertDontSee('Empat grup: Achilles');

        Carbon::setTestNow();
    }

    public function test_home_countdown_is_zero_while_a_match_is_live(): void
    {
        $this->seed(DatabaseSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-11-21 09:15:00', 'Asia/Jakarta'));

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $hercules = Team::query()->where('slug', 'hercules')->firstOrFail();
        $perseus = Team::query()->where('slug', 'perseus')->firstOrFail();
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $live = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'status' => 'live',
        ]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $achilles->id, 'sort_order' => 1, 'score_cache' => 2]);
        MatchTeam::query()->create(['match_id' => $live->id, 'team_id' => $odysseus->id, 'sort_order' => 2, 'score_cache' => 1]);

        $upcoming = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:30:00',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $hercules->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $perseus->id, 'sort_order' => 2]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-live="1"', false)
            ->assertSee('Pertandingan sedang berlangsung.')
            ->assertSee('Sedang berlangsung · Mini Soccer · Semifinal')
            ->assertDontSee('Menuju · Mini Soccer · Final');

        Carbon::setTestNow();
    }

    public function test_home_upcoming_section_excludes_live_matches(): void
    {
        $this->seed(DatabaseSeeder::class);

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $hercules = Team::query()->where('slug', 'hercules')->firstOrFail();
        $perseus = Team::query()->where('slug', 'perseus')->firstOrFail();
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

        $upcoming = GameMatch::query()->create([
            'game_id' => $soccer->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:30:00',
            'location' => 'GOR',
            'status' => 'upcoming',
        ]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $hercules->id, 'sort_order' => 1]);
        MatchTeam::query()->create(['match_id' => $upcoming->id, 'team_id' => $perseus->id, 'sort_order' => 2]);

        $html = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Live')
            ->assertSee('ACHILLES')
            ->assertSee('16:30')
            ->getContent();

        $start = strpos($html, 'Upcoming');
        $end = strpos($html, '>Games</h2>', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $upcomingHtml = substr($html, $start, $end - $start);

        $this->assertStringContainsString('HERCULES', $upcomingHtml);
        $this->assertStringContainsString('PERSEUS', $upcomingHtml);
        $this->assertStringContainsString('16:30', $upcomingHtml);
        $this->assertStringNotContainsString('Live', $upcomingHtml);
        $this->assertStringNotContainsString('ACHILLES', $upcomingHtml);
        $this->assertStringNotContainsString('ODYSSEUS', $upcomingHtml);
    }

    public function test_live_score_endpoint_is_public(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('live-score'))
            ->assertOk()
            ->assertJson(['live' => null]);
    }
}
