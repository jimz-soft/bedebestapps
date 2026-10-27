<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Setting::query()->create([
            'name' => 'BedeBest',
            'starts_at' => '2026-11-21 08:00:00',
            'ends_at' => '2026-11-22 18:00:00',
            'venue' => 'Aula utama',
            'hero_kicker' => 'Company Gathering 2026',
            'hero_highlight' => 'TOURNAMENT',
            'hero_body' => 'Countdown menuju pertandingan berikutnya.',
        ]);

        $teams = [
            ['name' => 'Achilles', 'slug' => 'achilles', 'code' => 'ACH', 'color' => '#8b5cf6', 'sort_order' => 1],
            ['name' => 'Odysseus', 'slug' => 'odysseus', 'code' => 'ODY', 'color' => '#06b6d4', 'sort_order' => 2],
            ['name' => 'Hercules', 'slug' => 'hercules', 'code' => 'HER', 'color' => '#d946ef', 'sort_order' => 3],
            ['name' => 'Perseus', 'slug' => 'perseus', 'code' => 'PER', 'color' => '#eab308', 'sort_order' => 4],
        ];

        foreach ($teams as $team) {
            Team::query()->create($team);
        }

        $games = [
            [
                'name' => 'Mini Soccer',
                'slug' => 'mini-soccer',
                'format' => 'head_to_head',
                'requires_roster' => true,
                'uses_period_score' => true,
                'period_label' => 'Babak',
                'period_count_default' => 2,
                'score_mode' => 'aggregate',
                'rules_html' => '<p>Sistem gugur. Skor dijumlahkan per babak. Roster diisi sebelum pertandingan selesai.</p>',
            ],
            [
                'name' => 'Voli',
                'slug' => 'voli',
                'format' => 'round_robin',
                'requires_roster' => true,
                'uses_period_score' => true,
                'period_label' => 'Set',
                'period_count_default' => 3,
                'score_mode' => 'sets',
                'rules_html' => '<p>Semua grup saling ketemu. Pemenang set yang dihitung, bukan jumlah angka.</p>',
            ],
            [
                'name' => 'Mukbang',
                'slug' => 'mukbang',
                'format' => 'heat',
                'requires_roster' => false,
                'uses_period_score' => false,
                'heat_size' => 4,
                'rules_html' => '<p>Empat grup tampil bersama. Panitia mengisi poin masing-masing grup.</p>',
            ],
        ];

        foreach ($games as $game) {
            Game::query()->create($game);
        }

        User::query()->create([
            'name' => 'Panitia',
            'username' => 'panitia',
            'email' => 'panitia@bedebest.local',
            'password' => 'password',
            'role' => User::ROLE_ADMINISTRATOR,
            'is_active' => true,
        ]);
    }
}
