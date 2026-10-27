<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Setting;
use App\Models\Team;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function edit(): View
    {
        return view('panitia.banners', [
            'setting' => Setting::query()->firstOrFail(),
            'teams' => Team::query()->orderBy('sort_order')->get(),
            'games' => Game::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'hero_banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'team_banners' => ['nullable', 'array'],
            'team_banners.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'game_banners' => ['nullable', 'array'],
            'game_banners.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $setting = Setting::query()->firstOrFail();

        if ($request->hasFile('hero_banner')) {
            $setting->hero_banner_path = Media::store(
                $request->file('hero_banner'),
                'banners/hero',
                $setting->hero_banner_path,
            );
            $setting->save();
        }

        foreach (Team::query()->orderBy('sort_order')->get() as $team) {
            $file = $request->file('team_banners.'.$team->id);

            if ($file === null) {
                continue;
            }

            $team->banner_path = Media::store($file, 'banners/teams', $team->banner_path);
            $team->save();
        }

        foreach (Game::query()->orderBy('name')->get() as $game) {
            $file = $request->file('game_banners.'.$game->id);

            if ($file === null) {
                continue;
            }

            $game->banner_path = Media::store($file, 'banners/games', $game->banner_path);
            $game->save();
        }

        return back()->with('status', 'Banner sudah disimpan.');
    }
}
