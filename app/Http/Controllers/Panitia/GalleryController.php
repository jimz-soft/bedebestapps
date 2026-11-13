<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameGalleryPhoto;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $games = Game::query()
            ->withCount('galleryPhotos')
            ->orderBy('name')
            ->get();

        return view('panitia.gallery.index', [
            'games' => $games,
        ]);
    }

    public function show(Game $game): View
    {
        $game->load(['galleryPhotos' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('panitia.gallery.show', [
            'game' => $game,
        ]);
    }

    public function store(Request $request, Game $game): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:120'],
        ]);

        $nextOrder = (int) $game->galleryPhotos()->max('sort_order');

        foreach ($request->file('photos', []) as $index => $file) {
            $nextOrder++;
            $caption = $request->input('captions.'.$index);
            $caption = is_string($caption) ? trim($caption) : null;

            GameGalleryPhoto::query()->create([
                'game_id' => $game->id,
                'path' => Media::store($file, 'gallery/games/'.$game->id),
                'caption' => $caption !== '' ? $caption : null,
                'sort_order' => $nextOrder,
            ]);
        }

        return redirect()
            ->route('panitia.gallery.show', $game)
            ->with('status', 'Foto gallery '.$game->name.' sudah diunggah.');
    }

    public function destroy(Game $game, GameGalleryPhoto $photo): RedirectResponse
    {
        abort_unless($photo->game_id === $game->id, 404);

        Media::delete($photo->path);
        $photo->delete();

        return redirect()
            ->route('panitia.gallery.show', $game)
            ->with('status', 'Foto gallery dihapus.');
    }
}
