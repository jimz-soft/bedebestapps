<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchRoster;
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
            ->get(route('panitia.dashboard'))
            ->assertOk()
            ->assertSee('id="sidebar-toggle"', false)
            ->assertSee('id="app-sidebar"', false)
            ->assertSee('Buka menu');

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
            'zoom_link' => 'https://zoom.us/j/987654321',
            'status' => 'live',
            'team_ids' => $teams->pluck('id')->all(),
        ])->assertRedirect(route('panitia.matches.index'));

        $match = GameMatch::query()->firstOrFail();
        $this->assertSame('https://zoom.us/j/987654321', $match->zoom_link);
        $match->load('matchTeams');
        $period = $match->periods()->firstOrFail();
        $home = $match->matchTeams->firstWhere('team_id', $teams[0]->id);
        $away = $match->matchTeams->firstWhere('team_id', $teams[1]->id);

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $teams[0]->id,
            'name' => 'Andika Pratama',
            'leadership' => 'captain',
        ])->assertRedirect();

        $this->assertDatabaseHas('participants', [
            'name' => 'Andika Pratama',
            'is_captain' => 1,
            'is_co_captain' => 0,
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
            'leadership' => 'captain',
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
        $opponent = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $mukbang = Game::query()->where('slug', 'mukbang')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Andika Pratama',
            'leadership' => 'captain',
        ]);
        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Budi Santoso',
            'leadership' => 'co_captain',
        ]);

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'upcoming',
            'team_ids' => [$team->id, $opponent->id],
        ])->assertRedirect();

        $match = GameMatch::query()->where('game_id', $game->id)->firstOrFail();
        $andika = Participant::query()->where('name', 'Andika Pratama')->firstOrFail();
        $budi = Participant::query()->where('name', 'Budi Santoso')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('panitia.rosters.edit', ['game_id' => $game->id, 'match_id' => $match->id, 'team_id' => $team->id]))
            ->assertOk()
            ->assertSee('Pilih roster')
            ->assertSee('Jadwal pertandingan')
            ->assertSee('Cari nama peserta')
            ->assertSee('id="roster-search"', false)
            ->assertSee('name="game_id"', false)
            ->assertSee('name="match_id"', false)
            ->assertSee('name="team_id"', false)
            ->assertSee('Andika Pratama')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Mukbang');

        $this->actingAs($admin)->put(route('panitia.rosters.update'), [
            'match_id' => $match->id,
            'team_id' => $team->id,
            'participant_ids' => [$andika->id, $budi->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('match_rosters', [
            'match_id' => $match->id,
            'team_id' => $team->id,
            'participant_id' => $andika->id,
        ]);
        $this->assertSame(2, MatchRoster::query()->where('match_id', $match->id)->where('team_id', $team->id)->count());

        $this->actingAs($admin)->from(route('panitia.rosters.edit'))
            ->put(route('panitia.rosters.update'), [
                'match_id' => 999999,
                'team_id' => $team->id,
                'participant_ids' => [$andika->id],
            ])
            ->assertSessionHasErrors('match_id');

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $mukbang->id,
            'round_label' => 'Heat 1',
            'scheduled_at' => '2026-11-21 18:00:00',
            'status' => 'upcoming',
            'team_ids' => Team::query()->orderBy('sort_order')->pluck('id')->all(),
        ])->assertRedirect();

        $mukbangMatch = GameMatch::query()->where('game_id', $mukbang->id)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('panitia.rosters.edit'))
            ->assertOk()
            ->assertDontSee('Mukbang');

        $this->actingAs($admin)->from(route('panitia.rosters.edit'))
            ->put(route('panitia.rosters.update'), [
                'match_id' => $mukbangMatch->id,
                'team_id' => $team->id,
                'participant_ids' => [$andika->id],
            ])
            ->assertSessionHasErrors('match_id');
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

        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'location' => 'Lapangan',
            'status' => 'upcoming',
            'team_ids' => [$achilles->id, $odysseus->id],
        ])->assertRedirect();

        $match = GameMatch::query()->where('game_id', $game->id)->firstOrFail();
        $andika = Participant::query()->where('name', 'Andika Pratama')->firstOrFail();
        $citra = Participant::query()->where('name', 'Citra Lestari')->firstOrFail();
        $pic = User::factory()->member()->create([
            'username' => 'pic_achilles',
            'team_id' => $achilles->id,
        ]);

        $this->actingAs($pic)->get(route('pic.rosters.edit', ['game_id' => $game->id, 'match_id' => $match->id]))
            ->assertOk()
            ->assertSee('Jadwal pertandingan')
            ->assertSee('name="game_id"', false)
            ->assertDontSee('>Grup</label>')
            ->assertSee('Andika Pratama')
            ->assertDontSee('Citra Lestari');

        $this->actingAs($pic)->put(route('pic.rosters.update'), [
            'match_id' => $match->id,
            'team_id' => $odysseus->id,
            'participant_ids' => [$citra->id],
        ])->assertSessionHasErrors('participant_ids');

        $this->actingAs($pic)->put(route('pic.rosters.update'), [
            'match_id' => $match->id,
            'team_id' => $odysseus->id,
            'participant_ids' => [$andika->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('match_rosters', [
            'match_id' => $match->id,
            'team_id' => $achilles->id,
            'participant_id' => $andika->id,
        ]);
        $this->assertDatabaseMissing('match_rosters', [
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
            'roster_min' => 1,
            'roster_max' => 11,
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
            'roster_min' => 1,
            'roster_max' => 11,
            'uses_period_score' => '1',
            'period_label' => 'Babak',
            'period_count_default' => 2,
            'score_mode' => 'aggregate',
            'is_active' => '1',
        ])->assertRedirect(route('panitia.games.index'));

        $this->assertTrue($game->fresh()->is_active);
        $this->get(route('home'))->assertSee('Mini Soccer');
    }

    public function test_roster_game_can_configure_min_and_max_and_enforces_them(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $team = Team::query()->where('slug', 'achilles')->firstOrFail();

        $this->actingAs($admin)->put(route('panitia.games.update', $game), [
            'name' => $game->name,
            'format' => $game->format,
            'requires_roster' => '1',
            'roster_min' => 2,
            'roster_max' => 3,
            'uses_period_score' => '1',
            'period_label' => 'Babak',
            'period_count_default' => 2,
            'score_mode' => 'aggregate',
            'is_active' => '1',
        ])->assertRedirect(route('panitia.games.index'));

        $game->refresh();
        $this->assertSame(2, $game->roster_min);
        $this->assertSame(3, $game->roster_max);

        $this->actingAs($admin)->get(route('panitia.games.index'))
            ->assertOk()
            ->assertSee('On (2–3)');

        foreach (['Andika Pratama', 'Budi Santoso', 'Citra Lestari', 'Dewi Anggraini'] as $name) {
            $this->actingAs($admin)->post(route('panitia.participants.store'), [
                'team_id' => $team->id,
                'name' => $name,
            ]);
        }

        $opponent = Team::query()->where('slug', 'odysseus')->firstOrFail();
        $this->actingAs($admin)->post(route('panitia.matches.store'), [
            'game_id' => $game->id,
            'round_label' => 'Semifinal',
            'scheduled_at' => '2026-11-21 09:00:00',
            'status' => 'upcoming',
            'team_ids' => [$team->id, $opponent->id],
        ])->assertRedirect();

        $match = GameMatch::query()->where('game_id', $game->id)->firstOrFail();
        $ids = Participant::query()->where('team_id', $team->id)->orderBy('id')->pluck('id');

        $this->actingAs($admin)
            ->get(route('panitia.rosters.edit', ['match_id' => $match->id, 'team_id' => $team->id]))
            ->assertOk()
            ->assertSee('minimum 2')
            ->assertSee('maximum 3')
            ->assertSee('data-min="2"', false)
            ->assertSee('data-max="3"', false)
            ->assertSee('Jumlah dipilih harus di dalam rentang itu');

        $this->actingAs($admin)->from(route('panitia.rosters.edit', ['match_id' => $match->id, 'team_id' => $team->id]))
            ->put(route('panitia.rosters.update'), [
                'match_id' => $match->id,
                'team_id' => $team->id,
                'participant_ids' => [$ids[0]],
            ])
            ->assertSessionHasErrors('participant_ids');

        $this->actingAs($admin)->from(route('panitia.rosters.edit', ['match_id' => $match->id, 'team_id' => $team->id]))
            ->put(route('panitia.rosters.update'), [
                'match_id' => $match->id,
                'team_id' => $team->id,
                'participant_ids' => $ids->take(4)->all(),
            ])
            ->assertSessionHasErrors('participant_ids');

        $this->actingAs($admin)->put(route('panitia.rosters.update'), [
            'match_id' => $match->id,
            'team_id' => $team->id,
            'participant_ids' => $ids->take(2)->all(),
        ])->assertRedirect();

        $this->assertSame(2, MatchRoster::query()->where('match_id', $match->id)->where('team_id', $team->id)->count());
    }

    public function test_administrator_can_bulk_add_and_import_participants(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $team = Team::query()->where('slug', 'achilles')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('panitia.participants.index', ['team_id' => $team->id]))
            ->assertOk()
            ->assertSee('Nama peserta (bisa banyak sekaligus)')
            ->assertSee('import dari file');

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'names' => "Andika Pratama\nBudi Santoso\nCitra Lestari\nAndika Pratama",
        ])->assertRedirect(route('panitia.participants.index', ['team_id' => $team->id]));

        $this->assertSame(3, Participant::query()->where('team_id', $team->id)->count());
        $this->assertDatabaseHas('participants', [
            'team_id' => $team->id,
            'name' => 'Andika Pratama',
        ]);

        $csv = UploadedFile::fake()->createWithContent(
            'peserta.csv',
            "Nama\nDewi Anggraini\nEko Saputra\nBudi Santoso\n"
        );

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'import_file' => $csv,
        ])->assertRedirect(route('panitia.participants.index', ['team_id' => $team->id]));

        $this->assertSame(5, Participant::query()->where('team_id', $team->id)->count());
        $this->assertDatabaseHas('participants', [
            'team_id' => $team->id,
            'name' => 'Dewi Anggraini',
        ]);
        $this->assertDatabaseHas('participants', [
            'team_id' => $team->id,
            'name' => 'Eko Saputra',
        ]);
    }

    public function test_administrator_can_assign_one_captain_and_one_co_captain_per_team(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $team = Team::query()->where('slug', 'achilles')->firstOrFail();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Andika Pratama',
            'leadership' => 'captain',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('panitia.participants.store'), [
            'team_id' => $team->id,
            'name' => 'Sari Wulandari',
            'leadership' => 'co_captain',
        ])->assertRedirect();

        $this->actingAs($admin)->from(route('panitia.participants.index', ['team_id' => $team->id]))
            ->post(route('panitia.participants.store'), [
                'team_id' => $team->id,
                'name' => 'Budi Santoso',
                'leadership' => 'captain',
            ])
            ->assertRedirect(route('panitia.participants.index', ['team_id' => $team->id]))
            ->assertSessionHasErrors('leadership');

        $this->actingAs($admin)->from(route('panitia.participants.index', ['team_id' => $team->id]))
            ->post(route('panitia.participants.store'), [
                'team_id' => $team->id,
                'name' => 'Citra Lestari',
                'leadership' => 'co_captain',
            ])
            ->assertRedirect(route('panitia.participants.index', ['team_id' => $team->id]))
            ->assertSessionHasErrors('leadership');

        $this->assertSame(1, Participant::query()->where('team_id', $team->id)->where('is_captain', true)->count());
        $this->assertSame(1, Participant::query()->where('team_id', $team->id)->where('is_co_captain', true)->count());
    }
}
