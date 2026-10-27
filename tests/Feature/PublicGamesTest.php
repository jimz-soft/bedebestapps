<?php

namespace Tests\Feature;

use App\Models\Game;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_games_page_lists_active_catalog_with_rules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('games'))
            ->assertOk()
            ->assertSee('GAMES')
            ->assertSee('Mini Soccer')
            ->assertSee('Voli')
            ->assertSee('Mukbang')
            ->assertSee('head-to-head')
            ->assertSee('sistem gugur')
            ->assertSee('Roster:')
            ->assertSee('aktif')
            ->assertSee('tidak aktif')
            ->assertSee('Sistem gugur')
            ->assertSee('Semua grup saling ketemu')
            ->assertSee('Lihat jadwal')
            ->assertSee('Banner Mini Soccer')
            ->assertSee('images/games/mini-soccer.jpg')
            ->assertDontSee('Halaman ini menyusul');
    }

    public function test_inactive_games_are_hidden_and_banners_render(): void
    {
        $this->seed(DatabaseSeeder::class);
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $voli = Game::query()->where('slug', 'voli')->firstOrFail();

        $soccer->update(['is_active' => false]);
        $voli->update(['banner_path' => 'banners/games/voli.jpg']);

        $this->get(route('games'))
            ->assertOk()
            ->assertDontSeeText('Mini Soccer')
            ->assertSee('Voli')
            ->assertSee('storage/banners/games/voli.jpg')
            ->assertSee('id="voli"', false);
    }
}
