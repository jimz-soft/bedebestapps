<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanitiaBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_banner_form(): void
    {
        $this->get(route('panitia.banners.edit'))->assertRedirect(route('login'));
    }

    public function test_panitia_can_replace_home_and_team_banners(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $panitia = User::query()->where('username', 'panitia')->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $team = Team::query()->where('slug', 'achilles')->firstOrFail();

        $this->actingAs($panitia)
            ->put(route('panitia.banners.update'), [
                'hero_banner' => UploadedFile::fake()->create('hero.jpg', 200, 'image/jpeg'),
                'team_banners' => [
                    $team->id => UploadedFile::fake()->create('achilles.jpg', 200, 'image/jpeg'),
                ],
            ])
            ->assertRedirect();

        $setting = Setting::query()->firstOrFail();

        $this->assertNotNull($setting->hero_banner_path);
        Storage::disk('public')->assertExists($setting->hero_banner_path);

        $team->refresh();
        $this->assertNotNull($team->banner_path);
        Storage::disk('public')->assertExists($team->banner_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('storage/'.$setting->hero_banner_path)
            ->assertSee('storage/'.$team->banner_path);
    }

    public function test_panitia_can_replace_game_banners_on_public_games_page(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $panitia = User::query()->where('username', 'panitia')->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $this->actingAs($panitia)
            ->put(route('panitia.banners.update'), [
                'game_banners' => [
                    $game->id => UploadedFile::fake()->create('soccer.jpg', 200, 'image/jpeg'),
                ],
            ])
            ->assertRedirect();

        $game->refresh();
        $this->assertNotNull($game->banner_path);
        Storage::disk('public')->assertExists($game->banner_path);

        $this->get(route('games'))
            ->assertOk()
            ->assertSee('storage/'.$game->banner_path)
            ->assertSee('Banner Mini Soccer');
    }
}
