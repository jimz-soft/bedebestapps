<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        return view('panitia.grup.index', [
            'teams' => Team::query()
                ->withCount(['participants', 'users'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('panitia.grup.form', [
            'team' => new Team([
                'color' => '#8b5cf6',
                'sort_order' => (int) Team::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['color'] = $this->normalizeColor($data['color'] ?? null);
        $data['sort_order'] = (int) ($data['sort_order'] ?? Team::query()->max('sort_order') + 1);
        unset($data['logo'], $data['banner']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = Media::store($request->file('logo'), 'logos/teams');
        }

        if ($request->hasFile('banner')) {
            $data['banner_path'] = Media::store($request->file('banner'), 'banners/teams');
        }

        Team::query()->create($data);

        return redirect()->route('panitia.grup.index')->with('status', 'Master grup sudah disimpan.');
    }

    public function edit(Team $team): View
    {
        return view('panitia.grup.form', ['team' => $team]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $data = $this->validated($request, $team);
        $data['color'] = $this->normalizeColor($data['color'] ?? null);
        $data['sort_order'] = (int) ($data['sort_order'] ?? $team->sort_order);
        unset($data['logo'], $data['banner']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = Media::store($request->file('logo'), 'logos/teams', $team->logo_path);
        }

        if ($request->hasFile('banner')) {
            $data['banner_path'] = Media::store($request->file('banner'), 'banners/teams', $team->banner_path);
        }

        $team->update($data);

        return redirect()->route('panitia.grup.index')->with('status', 'Master grup sudah diubah.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        if ($team->participants()->exists() || $team->users()->exists() || $team->matchTeams()->exists()) {
            return back()->withErrors([
                'team' => 'Grup masih dipakai peserta, user, atau pertandingan.',
            ]);
        }

        $team->delete();

        return redirect()->route('panitia.grup.index')->with('status', 'Grup sudah dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Team $team = null): array
    {
        $request->merge([
            'code' => strtoupper((string) $request->input('code')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:8', 'alpha_num', Rule::unique('teams', 'code')->ignore($team)],
            'color' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'motto' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'grup';
        $slug = $base;
        $i = 1;

        while (Team::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function normalizeColor(?string $color): string
    {
        $color = trim((string) $color);

        if ($color === '') {
            return '#8b5cf6';
        }

        return str_starts_with($color, '#') ? $color : '#'.$color;
    }
}
