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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdministratorPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_deactivate_and_change_user_role(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $team = Team::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('panitia.users.create'))
            ->assertOk()
            ->assertSee('Administrator');

        $this->actingAs($admin)->post(route('panitia.users.store'), [
            'name' => 'PIC Achilles',
            'username' => 'pic_achilles',
            'password' => 'secret1',
            'role' => User::ROLE_USER,
            'team_id' => $team->id,
            'is_active' => '1',
        ])->assertRedirect(route('panitia.users.index'));

        $member = User::query()->where('username', 'pic_achilles')->firstOrFail();
        $this->assertSame(User::ROLE_USER, $member->role);
        $this->assertTrue($member->is_active);
        $this->assertSame($team->id, $member->team_id);

        $this->actingAs($admin)->put(route('panitia.users.update', $member), [
            'name' => 'Koordinator lapangan',
            'username' => 'pic_achilles',
            'role' => User::ROLE_ADMINISTRATOR,
            'is_active' => '1',
        ])->assertRedirect(route('panitia.users.index'));

        $member->refresh();
        $this->assertSame(User::ROLE_ADMINISTRATOR, $member->role);
        $this->assertNull($member->team_id);

        $this->actingAs($admin)->put(route('panitia.users.update', $member), [
            'name' => 'Koordinator lapangan',
            'username' => 'pic_achilles',
            'role' => User::ROLE_USER,
            'team_id' => $team->id,
        ])->assertRedirect(route('panitia.users.index'));

        $this->assertFalse($member->fresh()->is_active);
        $this->assertSame(User::ROLE_USER, $member->fresh()->role);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->member()->inactive()->create([
            'username' => 'nonaktif',
            'password' => 'secret1',
        ]);

        $this->post(route('login.store'), [
            'username' => 'nonaktif',
            'password' => 'secret1',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertFalse($user->is_active);
    }

    public function test_regular_user_cannot_open_administrator_panel(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->member()->create();

        $this->actingAs($user)->get(route('panitia.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('panitia.grup.index'))->assertForbidden();
        $this->actingAs($user)->get(route('panitia.games.index'))->assertForbidden();
        $this->actingAs($user)->get(route('panitia.scores.index'))->assertForbidden();
        $this->actingAs($user)->get(route('panitia.rosters.edit'))->assertForbidden();
        $this->actingAs($user)->get(route('pic.jadwal'))->assertForbidden();
    }

    public function test_administrator_can_manage_games_schedule_participants_and_scores(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $teams = Team::query()->orderBy('sort_order')->take(2)->get();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.games.store'), [
            'name' => 'Estafet',
            'format' => 'heat',
            'heat_size' => 4,
            'rules_html' => '<p>Lari estafet antar grup.</p>',
        ])->assertRedirect(route('panitia.games.index'));

        $this->assertDatabaseHas('games', ['name' => 'Estafet', 'format' => 'heat']);

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'live',
            'team_ids' => $teams->pluck('id')->all(),
        ])->assertRedirect(route('panitia.matches.index'));

        $match = GameMatch::query()->firstOrFail();
        $match->load('matchTeams');
        $period = $match->periods()->firstOrFail();
        $home = $match->matchTeams->firstWhere('team_id', $teams[0]->id);
        $away = $match->matchTeams->firstWhere('team_id', $teams[1]->id);

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $teams[0]->id,
            'name' => 'Andika Pratama',
            'is_captain' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('participants', [
            'name' => 'Andika Pratama',
            'is_captain' => 1,
        ]);

        $this->actingAs($admin)->put(route('panitia.scores.update', $match), [
            'status' => 'live',
            'periods' => [
                $period->id => [
                    'id' => $period->id,
                    'status' => 'running',
                    'scores' => [
                        $teams[0]->id => 2,
                        $teams[1]->id => 1,
                    ],
                ],
            ],
            'teams' => [
                $home->id => [
                    'points_awarded' => 10,
                    'poin_personil' => 4,
                    'bonus_pemenang' => 5,
                    'bonus_keterangan' => 'Bonus semifinal',
                    'result' => 'win',
                ],
                $away->id => [
                    'points_awarded' => 4,
                    'poin_personil' => 3,
                    'bonus_pemenang' => 0,
                    'bonus_keterangan' => '',
                    'result' => 'loss',
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('match_teams', [
            'match_id' => $match->id,
            'team_id' => $teams[0]->id,
            'score_cache' => 2,
        ]);

        $this->get(route('live-score'))
            ->assertOk()
            ->assertJsonPath('live.game', 'Mini Soccer')
            ->assertJsonPath('live.sides.0.score', 2)
            ->assertJsonPath('live.sides.1.score', 1);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('live-score')
            ->assertSee('2')
            ->assertSee('1');

        $this->actingAs($admin)->post(route('panitia.scores.finish', $match))->assertRedirect();

        $this->assertSame('selesai', $match->fresh()->status);
        $this->assertDatabaseHas('match_teams', [
            'match_id' => $match->id,
            'team_id' => $teams[0]->id,
            'points_awarded' => 10,
            'poin_personil' => 4,
            'bonus_pemenang' => 5,
            'bonus_keterangan' => 'Bonus semifinal',
        ]);
    }

    public function test_winner_bonus_requires_description(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $teams = Team::query()->orderBy('sort_order')->take(2)->get();
        $game = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Sesi 1',
            'scheduled_at' => '2026-11-21 12:00:00',
            'status' => 'live',
            'team_ids' => $teams->pluck('id')->all(),
        ])->assertRedirect(route('panitia.matches.index'));

        $match = GameMatch::query()->firstOrFail()->load('matchTeams');
        $home = $match->matchTeams->first();

        $this->actingAs($admin)->from(route('panitia.scores.edit', $match))
            ->followingRedirects()
            ->put(route('panitia.scores.update', $match), [
                'status' => 'live',
                'teams' => [
                    $home->id => [
                        'points_awarded' => 8,
                        'poin_personil' => 2,
                        'bonus_pemenang' => 3,
                        'bonus_keterangan' => '',
                    ],
                ],
            ])
            ->assertOk()
            ->assertSee('Simpan ditolak')
            ->assertSee('Isi keterangan bonus');
    }

    public function test_finish_button_saves_personnel_points_and_bonus(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $teams = Team::query()->orderBy('sort_order')->take(2)->get();
        $game = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Sesi 1',
            'scheduled_at' => '2026-11-21 12:00:00',
            'status' => 'live',
            'team_ids' => $teams->pluck('id')->all(),
        ])->assertRedirect(route('panitia.matches.index'));

        $match = GameMatch::query()->firstOrFail()->load('matchTeams');
        $home = $match->matchTeams[0];
        $away = $match->matchTeams[1];

        $this->actingAs($admin)->put(route('panitia.scores.update', $match), [
            'status' => 'live',
            'intent' => 'finish',
            'teams' => [
                $home->id => [
                    'points_awarded' => 12,
                    'poin_personil' => 7,
                    'bonus_pemenang' => 3,
                    'bonus_keterangan' => 'Bonus juara sesi',
                    'result' => 'win',
                ],
                $away->id => [
                    'points_awarded' => 8,
                    'poin_personil' => 4,
                    'bonus_pemenang' => 0,
                    'bonus_keterangan' => '',
                    'result' => 'loss',
                ],
            ],
        ])->assertRedirect();

        $this->assertSame('selesai', $match->fresh()->status);
        $this->assertDatabaseHas('match_teams', [
            'id' => $home->id,
            'poin_personil' => 7,
            'bonus_pemenang' => 3,
            'bonus_keterangan' => 'Bonus juara sesi',
        ]);

        $this->actingAs($admin)->put(route('panitia.scores.update', $match), [
            'status' => 'selesai',
            'teams' => [
                $home->id => [
                    'points_awarded' => 12,
                    'poin_personil' => 9,
                    'bonus_pemenang' => 5,
                    'bonus_keterangan' => 'Bonus juara sesi direvisi',
                    'result' => 'win',
                ],
                $away->id => [
                    'points_awarded' => 8,
                    'poin_personil' => 4,
                    'bonus_pemenang' => 0,
                    'bonus_keterangan' => '',
                    'result' => 'loss',
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('match_teams', [
            'id' => $home->id,
            'poin_personil' => 9,
            'bonus_pemenang' => 5,
        ]);

        $this->actingAs($admin)
            ->get(route('panitia.scores.edit', $match))
            ->assertOk()
            ->assertSee($home->team->name)
            ->assertSee('Pemenang')
            ->assertSee('★');
    }

    public function test_administrator_can_maintain_master_groups(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.grup.store'), [
            'name' => 'Theseus',
            'code' => 'the',
            'color' => '#22c55e',
            'motto' => 'Maju terus',
            'sort_order' => 5,
            'banner' => UploadedFile::fake()->create('theseus.jpg', 200, 'image/jpeg'),
        ])->assertRedirect(route('panitia.grup.index'));

        $group = Team::query()->where('slug', 'theseus')->firstOrFail();
        $this->assertSame('THE', $group->code);
        $this->assertSame('#22c55e', $group->color);
        $this->assertNotNull($group->banner_path);
        Storage::disk('public')->assertExists($group->banner_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Theseus')
            ->assertSee('storage/'.$group->banner_path);

        $this->actingAs($admin)->put(route('panitia.grup.update', $group), [
            'name' => 'Theseus Prime',
            'code' => 'THS',
            'color' => '16a34a',
            'motto' => 'Jangan mundur',
            'sort_order' => 6,
        ])->assertRedirect(route('panitia.grup.index'));

        $group->refresh();
        $this->assertSame('Theseus Prime', $group->name);
        $this->assertSame('THS', $group->code);
        $this->assertSame('#16a34a', $group->color);
        $this->assertSame(6, $group->sort_order);

        $this->actingAs($admin)->delete(route('panitia.grup.destroy', $group))
            ->assertRedirect(route('panitia.grup.index'));
        $this->assertDatabaseMissing('teams', ['id' => $group->id]);
    }

    public function test_administrator_cannot_delete_group_that_is_in_use(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $team = Team::query()->where('slug', 'achilles')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Kapten Achilles',
            'is_captain' => '1',
        ]);

        $this->actingAs($admin)->from(route('panitia.grup.index'))
            ->delete(route('panitia.grup.destroy', $team))
            ->assertRedirect(route('panitia.grup.index'))
            ->assertSessionHasErrors('team');

        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }

    public function test_administrator_can_pick_roster_from_team_participants(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $team = Team::query()->where('slug', 'achilles')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $mukbang = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Andika Pratama',
            'is_captain' => '1',
        ]);
        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Budi Santoso',
        ]);

        $andika = Participant::query()->where('name', 'Andika Pratama')->firstOrFail();
        $budi = Participant::query()->where('name', 'Budi Santoso')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('panitia.rosters.edit', ['game_id' => $game->id, 'team_id' => $team->id]))
            ->assertOk()
            ->assertSee('Pilih roster')
            ->assertSee('Andika Pratama')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Mukbang');

        $this->actingAs($admin)->put(route('panitia.rosters.update'), [
            'game_id' => $game->id,
            'team_id' => $team->id,
            'participant_ids' => [$andika->id, $budi->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('game_rosters', [
            'game_id' => $game->id,
            'team_id' => $team->id,
            'participant_id' => $andika->id,
        ]);
        $this->assertSame(2, GameRoster::query()->where('game_id', $game->id)->where('team_id', $team->id)->count());

        $this->actingAs($admin)->from(route('panitia.rosters.edit'))
            ->put(route('panitia.rosters.update'), [
                'game_id' => $mukbang->id,
                'team_id' => $team->id,
                'participant_ids' => [$andika->id],
            ])
            ->assertSessionHasErrors('game_id');
    }

    public function test_team_user_can_pick_own_roster_only(): void
    {
        $this->seed(DatabaseSeeder::class);
        $achilles = Team::query()->where('slug', 'achilles')->firstOrFail();
        $odysseus = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $admin = User::query()->where('username', 'panitia')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $achilles->id,
            'name' => 'Andika Pratama',
        ]);
        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $odysseus->id,
            'name' => 'Citra Lestari',
        ]);

        $andika = Participant::query()->where('name', 'Andika Pratama')->firstOrFail();
        $citra = Participant::query()->where('name', 'Citra Lestari')->firstOrFail();
        $pic = User::factory()->member()->create([
            'username' => 'pic_achilles',
            'team_id' => $achilles->id,
        ]);

        $this->actingAs($pic)->get(route('pic.rosters.edit'))
            ->assertOk()
            ->assertSee('Andika Pratama')
            ->assertDontSee('Citra Lestari');

        $this->actingAs($pic)->put(route('pic.rosters.update'), [
            'game_id' => $game->id,
            'team_id' => $odysseus->id,
            'participant_ids' => [$citra->id],
        ])->assertSessionHasErrors('participant_ids');

        $this->actingAs($pic)->put(route('pic.rosters.update'), [
            'game_id' => $game->id,
            'team_id' => $odysseus->id,
            'participant_ids' => [$andika->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('game_rosters', [
            'game_id' => $game->id,
            'team_id' => $achilles->id,
            'participant_id' => $andika->id,
        ]);
        $this->assertDatabaseMissing('game_rosters', [
            'team_id' => $odysseus->id,
            'participant_id' => $citra->id,
        ]);
    }

    public function test_administrator_can_deactivate_a_game(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $teams = Team::query()->orderBy('sort_order')->take(2)->get();

        $this->actingAs($admin)->put(route('panitia.games.update', $game), [
            'name' => $game->name,
            'format' => $game->format,
            'requires_roster' => '1',
            'uses_period_score' => '1',
            'period_label' => 'Babak',
            'period_count_default' => 2,
            'score_mode' => 'aggregate',
        ])->assertRedirect(route('panitia.games.index'));

        $this->assertFalse($game->fresh()->is_active);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSeeText('Mini Soccer');

        $this->actingAs($admin)
            ->get(route('panitia.rosters.edit'))
            ->assertOk()
            ->assertDontSeeText('Mini Soccer');

        $this->actingAs($admin)->from(route('panitia.matches.create'))
            ->post(route('panitia.matches.store'), [
                'game_id' => $game->id,
                'round_label' => 'Final',
                'scheduled_at' => '2026-11-21 16:00:00',
                'status' => 'upcoming',
                'team_ids' => $teams->pluck('id')->all(),
            ])
            ->assertRedirect(route('panitia.matches.create'))
            ->assertSessionHasErrors('game_id');

        $this->actingAs($admin)->put(route('panitia.games.update', $game), [
            'name' => $game->name,
            'format' => $game->format,
            'requires_roster' => '1',
            'uses_period_score' => '1',
            'period_label' => 'Babak',
            'period_count_default' => 2,
            'score_mode' => 'aggregate',
            'is_active' => '1',
        ])->assertRedirect(route('panitia.games.index'));

        $this->assertTrue($game->fresh()->is_active);
        $this->get(route('home'))->assertSee('Mini Soccer');
    }
}
