<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\GameRoster;
use App\Models\Participant;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_login_goes_to_team_schedule(): void
    {
        $this->seed(DatabaseSeeder::class);
        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        User::factory()->member()->create([
            'username' => 'kapten_achilles',
            'password' => 'password',
            'team_id' => $achilles->id,
        ]);

        $this->post(route('login.store'), [
            'username' => 'kapten_achilles',
            'password' => 'password',
        ])->assertRedirect(route('pic.jadwal'));
    }

    public function test_team_user_can_view_schedule_and_own_profile(): void
    {
        [$admin, $pic, $achilles, $odysseus, $game] = $this->seedPicContext();

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'upcoming',
            'team_ids' => [$achilles->id, $odysseus->id],
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $achilles->id,
            'name' => 'Andika Pratama',
            'is_captain' => '1',
        ]);
        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $odysseus->id,
            'name' => 'Citra Lestari',
        ]);

        $this->actingAs($pic)->get(route('pic.jadwal'))
            ->assertOk()
            ->assertSee('Jadwal')
            ->assertSee('Profil tim')
            ->assertSee('Pilih roster')
            ->assertSee('Mini Soccer')
            ->assertSee('Semifinal')
            ->assertSee('Achilles')
            ->assertDontSee('Master games')
            ->assertDontSee('Panel Panitia');

        $this->actingAs($pic)->get(route('pic.profil'))
            ->assertOk()
            ->assertSee('ACHILLES')
            ->assertSee('Andika Pratama')
            ->assertSee('Kapten')
            ->assertDontSee('Citra Lestari')
            ->assertSee('Mini Soccer')
            ->assertDontSee('Mukbang');

        $this->actingAs($pic)->get(route('panitia.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('pic.jadwal'))->assertForbidden();
    }

    public function test_roster_stays_editable_until_all_matches_for_that_game_are_finished(): void
    {
        [$admin, $pic, $achilles, $odysseus, $game] = $this->seedPicContext();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $achilles->id,
            'name' => 'Andika Pratama',
        ]);
        $andika = Participant::query()->where('name', 'Andika Pratama')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'upcoming',
            'team_ids' => [$achilles->id, $odysseus->id],
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Final',
            'scheduled_at' => '2026-11-21 16:00:00',
            'location' => 'Lapangan',
            'status' => 'upcoming',
            'team_ids' => [$achilles->id, $odysseus->id],
        ])->assertRedirect();

        $this->actingAs($pic)->get(route('pic.rosters.edit', ['game_id' => $game->id]))
            ->assertOk()
            ->assertSee('Andika Pratama')
            ->assertSee('Bisa diubah selama pertandingan')
            ->assertDontSee('Roster terkunci');

        $this->actingAs($pic)->put(route('pic.rosters.update'), [
            'game_id' => $game->id,
            'participant_ids' => [$andika->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('game_rosters', [
            'game_id' => $game->id,
            'team_id' => $achilles->id,
            'participant_id' => $andika->id,
        ]);

        $first = GameMatch::query()->orderBy('id')->firstOrFail();
        $second = GameMatch::query()->orderByDesc('id')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.scores.finish', $first))->assertRedirect();

        $this->actingAs($pic)->from(route('pic.rosters.edit', ['game_id' => $game->id]))
            ->put(route('pic.rosters.update'), [
                'game_id' => $game->id,
                'participant_ids' => [],
            ])
            ->assertRedirect();

        $this->assertSame(0, GameRoster::query()->where('game_id', $game->id)->where('team_id', $achilles->id)->count());

        $this->actingAs($pic)->put(route('pic.rosters.update'), [
            'game_id' => $game->id,
            'participant_ids' => [$andika->id],
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('panitia.scores.finish', $second))->assertRedirect();

        $this->actingAs($pic)->from(route('pic.rosters.edit', ['game_id' => $game->id]))
            ->put(route('pic.rosters.update'), [
                'game_id' => $game->id,
                'participant_ids' => [],
            ])
            ->assertSessionHasErrors('participant_ids');

        $this->assertDatabaseHas('game_rosters', [
            'game_id' => $game->id,
            'team_id' => $achilles->id,
            'participant_id' => $andika->id,
        ]);

        $this->actingAs($pic)->get(route('pic.rosters.edit', ['game_id' => $game->id]))
            ->assertOk()
            ->assertSee('Roster terkunci');
    }

    /**
     * @return array{0: User, 1: User, 2: Team, 3: Team, 4: Game}
     */
    private function seedPicContext(): array
    {
        $this->seed(DatabaseSeeder::class);

        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $pic = User::factory()->member()->create([
            'username' => 'kapten_achilles',
            'team_id' => $achilles->id,
        ]);

        return [$admin, $pic, $achilles, $odysseus, $game];
    }
}
