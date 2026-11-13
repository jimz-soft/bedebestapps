<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameGalleryPhoto;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_gallery_lists_game_thumbnails(): void
    {
        $this->seed(DatabaseSeeder::class);
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $voli = Game::query()->where('slug', 'voli')->firstOrFail();

        GameGalleryPhoto::query()->create([
            'game_id' => $soccer->id,
            'path' => 'gallery/games/'.$soccer->id.'/a.jpg',
            'caption' => 'Kickoff Mini Soccer',
            'sort_order' => 1,
        ]);
        GameGalleryPhoto::query()->create([
            'game_id' => $soccer->id,
            'path' => 'gallery/games/'.$soccer->id.'/b.jpg',
            'caption' => null,
            'sort_order' => 2,
        ]);

        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('GALLERY')
            ->assertSee('Mini Soccer')
            ->assertSee('2 foto')
            ->assertSee(route('gallery.show', $soccer), false)
            ->assertSee('storage/gallery/games/'.$soccer->id.'/a.jpg')
            ->assertDontSeeText('Mukbang')
            ->assertDontSee('Kolase foto');

        $this->get(route('gallery.show', $voli))->assertNotFound();
    }

    public function test_public_gallery_game_shows_photo_collage_grid(): void
    {
        $this->seed(DatabaseSeeder::class);
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $first = GameGalleryPhoto::query()->create([
            'game_id' => $soccer->id,
            'path' => 'gallery/games/'.$soccer->id.'/a.jpg',
            'caption' => 'Kickoff Mini Soccer',
            'sort_order' => 1,
        ]);
        GameGalleryPhoto::query()->create([
            'game_id' => $soccer->id,
            'path' => 'gallery/games/'.$soccer->id.'/b.jpg',
            'caption' => 'Gol Achilles',
            'sort_order' => 2,
        ]);

        $this->get(route('gallery.show', $soccer))
            ->assertOk()
            ->assertSee('Kolase foto')
            ->assertSee('MINI SOCCER')
            ->assertSee('Kickoff Mini Soccer')
            ->assertSee('Gol Achilles')
            ->assertSee('Download')
            ->assertSee(route('gallery.download', $first), false)
            ->assertSee(route('gallery'), false);
    }

    public function test_public_can_download_gallery_photo(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $soccer = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        Storage::disk('public')->put('gallery/games/'.$soccer->id.'/a.jpg', 'fake-image');

        $photo = GameGalleryPhoto::query()->create([
            'game_id' => $soccer->id,
            'path' => 'gallery/games/'.$soccer->id.'/a.jpg',
            'caption' => 'Kickoff',
            'sort_order' => 1,
        ]);

        $this->get(route('gallery.download', $photo))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_administrator_can_upload_and_delete_gallery_photos(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('panitia.gallery.index'))
            ->assertOk()
            ->assertSee('Gallery')
            ->assertSee('Mini Soccer');

        $this->actingAs($admin)
            ->post(route('panitia.gallery.store', $game), [
                'photos' => [
                    UploadedFile::fake()->create('one.jpg', 200, 'image/jpeg'),
                    UploadedFile::fake()->create('two.jpg', 200, 'image/jpeg'),
                ],
            ])
            ->assertRedirect(route('panitia.gallery.show', $game));

        $this->assertSame(2, GameGalleryPhoto::query()->where('game_id', $game->id)->count());
        $photo = GameGalleryPhoto::query()->where('game_id', $game->id)->firstOrFail();
        Storage::disk('public')->assertExists($photo->path);

        $this->actingAs($admin)
            ->get(route('panitia.gallery.show', $game))
            ->assertOk()
            ->assertSee('storage/'.$photo->path, false);

        $this->actingAs($admin)
            ->delete(route('panitia.gallery.destroy', [$game, $photo]))
            ->assertRedirect(route('panitia.gallery.show', $game));

        $this->assertDatabaseMissing('game_gallery_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($photo->path);
        $this->assertSame(1, GameGalleryPhoto::query()->where('game_id', $game->id)->count());
    }

    public function test_guest_and_team_user_cannot_manage_gallery(): void
    {
        $this->seed(DatabaseSeeder::class);
        $game = Game::query()->where('slug', 'mini-soccer')->firstOrFail();
        $pic = User::factory()->member()->create();

        $this->get(route('panitia.gallery.index'))->assertRedirect(route('login'));
        $this->actingAs($pic)->get(route('panitia.gallery.index'))->assertForbidden();
        $this->actingAs($pic)->get(route('panitia.gallery.show', $game))->assertForbidden();
    }
}
