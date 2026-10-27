<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('panitia.games.index', [
            'games' => Game::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('panitia.games.form', [
            'game' => new Game([
                'format' => 'head_to_head',
                'requires_roster' => true,
                'uses_period_score' => true,
                'period_label' => 'Babak',
                'period_count_default' => 2,
                'score_mode' => 'aggregate',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        unset($data['banner']);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['requires_roster'] = $request->boolean('requires_roster');
        $data['uses_period_score'] = $request->boolean('uses_period_score');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('banner')) {
            $data['banner_path'] = Media::store($request->file('banner'), 'banners/games');
        }

        Game::query()->create($data);

        return redirect()->route('panitia.games.index')->with('status', 'Master game sudah disimpan.');
    }

    public function edit(Game $game): View
    {
        return view('panitia.games.form', ['game' => $game]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $data = $this->validated($request);
        unset($data['banner']);
        $data['requires_roster'] = $request->boolean('requires_roster');
        $data['uses_period_score'] = $request->boolean('uses_period_score');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('banner')) {
            $data['banner_path'] = Media::store($request->file('banner'), 'banners/games', $game->banner_path);
        }

        $game->update($data);

        return redirect()->route('panitia.games.index')->with('status', 'Master game sudah diubah.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        $game->delete();

        return redirect()->route('panitia.games.index')->with('status', 'Game sudah dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'format' => ['required', Rule::in(['head_to_head', 'round_robin', 'heat'])],
            'requires_roster' => ['nullable', 'boolean'],
            'uses_period_score' => ['nullable', 'boolean'],
            'period_label' => ['nullable', 'string', 'max:30'],
            'period_count_default' => ['nullable', 'integer', 'min:1', 'max:9'],
            'score_mode' => ['nullable', Rule::in(['aggregate', 'sets'])],
            'heat_size' => ['nullable', 'integer', 'min:2', 'max:16'],
            'rules_html' => ['nullable', 'string'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'game';
        $slug = $base;
        $i = 1;

        while (Game::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
