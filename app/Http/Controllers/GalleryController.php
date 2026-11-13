<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameGalleryPhoto;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleryController extends Controller
{
    public function index(): View
    {
        $games = Game::query()
            ->active()
            ->withCount('galleryPhotos')
            ->with(['galleryPhotos' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')->limit(1)])
            ->whereHas('galleryPhotos')
            ->orderBy('name')
            ->get();

        return view('gallery.index', [
            'setting' => Setting::query()->first(),
            'games' => $games,
        ]);
    }

    public function show(Game $game): View
    {
        abort_unless($game->is_active, 404);

        $game->load(['galleryPhotos' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        abort_if($game->galleryPhotos->isEmpty(), 404);

        return view('gallery.show', [
            'setting' => Setting::query()->first(),
            'game' => $game,
        ]);
    }

    public function download(GameGalleryPhoto $photo): StreamedResponse
    {
        $photo->loadMissing('game');

        abort_unless(
            $photo->game !== null
            && $photo->game->is_active
            && Storage::disk('public')->exists($photo->path),
            404
        );

        $extension = pathinfo($photo->path, PATHINFO_EXTENSION) ?: 'jpg';
        $slug = $photo->game->slug ?: 'gallery';
        $filename = $slug.'-'.$photo->id.'.'.$extension;

        return Storage::disk('public')->download($photo->path, $filename);
    }
}
