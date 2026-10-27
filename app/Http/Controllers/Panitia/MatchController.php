<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchPeriod;
use App\Models\MatchPeriodScore;
use App\Models\MatchTeam;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        return view('panitia.matches.index', [
            'matches' => GameMatch::query()->with(['game', 'matchTeams.team'])->latest('scheduled_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('panitia.matches.form', [
            'match' => new GameMatch(['status' => 'upcoming']),
            'games' => $this->gamesForForm(),
            'teams' => Team::query()->orderBy('sort_order')->get(),
            'selectedTeamIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $game = $this->activeGame($data['game_id']);
        $teamIds = $this->teamIds($request, $game);

        $match = GameMatch::query()->create([
            'game_id' => $game->id,
            'round_label' => $data['round_label'],
            'scheduled_at' => $data['scheduled_at'],
            'location' => $data['location'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->syncTeamsAndPeriods($match, $game, $teamIds);

        return redirect()->route('panitia.matches.index')->with('status', 'Jadwal pertandingan sudah disimpan.');
    }

    public function edit(GameMatch $match): View
    {
        $match->load('matchTeams');

        return view('panitia.matches.form', [
            'match' => $match,
            'games' => $this->gamesForForm($match),
            'teams' => Team::query()->orderBy('sort_order')->get(),
            'selectedTeamIds' => $match->matchTeams->pluck('team_id')->all(),
        ]);
    }

    public function update(Request $request, GameMatch $match): RedirectResponse
    {
        $data = $this->validated($request);
        $game = $this->activeGame($data['game_id'], $match);
        $teamIds = $this->teamIds($request, $game);

        $match->update([
            'game_id' => $game->id,
            'round_label' => $data['round_label'],
            'scheduled_at' => $data['scheduled_at'],
            'location' => $data['location'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        if ($match->status !== 'selesai') {
            $this->syncTeamsAndPeriods($match, $game, $teamIds);
        }

        return redirect()->route('panitia.matches.index')->with('status', 'Jadwal pertandingan sudah diubah.');
    }

    public function destroy(GameMatch $match): RedirectResponse
    {
        $match->delete();

        return redirect()->route('panitia.matches.index')->with('status', 'Jadwal sudah dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'round_label' => ['required', 'string', 'max:80'],
            'scheduled_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['upcoming', 'live', 'selesai'])],
            'notes' => ['nullable', 'string'],
            'team_ids' => ['required', 'array', 'min:2'],
            'team_ids.*' => ['exists:teams,id'],
        ]);
    }

    /**
     * @return list<int>
     */
    private function teamIds(Request $request, Game $game): array
    {
        $teamIds = collect($request->input('team_ids', []))->map(fn ($id) => (int) $id)->unique()->values();

        if ($game->format !== 'heat' && $teamIds->count() !== 2) {
            throw ValidationException::withMessages([
                'team_ids' => 'Format duel memakai dua tim.',
            ]);
        }

        if ($game->format === 'heat' && $teamIds->count() < 2) {
            throw ValidationException::withMessages([
                'team_ids' => 'Lomba bersama minimal dua tim.',
            ]);
        }

        return $teamIds->all();
    }

    /**
     * @param  list<int>  $teamIds
     */
    private function syncTeamsAndPeriods(GameMatch $match, Game $game, array $teamIds): void
    {
        $match->matchTeams()->whereNotIn('team_id', $teamIds)->delete();

        foreach ($teamIds as $index => $teamId) {
            MatchTeam::query()->updateOrCreate(
                ['match_id' => $match->id, 'team_id' => $teamId],
                ['sort_order' => $index + 1],
            );
        }

        if (! $game->uses_period_score) {
            $match->periods()->delete();

            return;
        }

        $count = max(1, (int) $game->period_count_default);

        for ($periodNo = 1; $periodNo <= $count; $periodNo++) {
            $period = MatchPeriod::query()->firstOrCreate(
                ['match_id' => $match->id, 'period_no' => $periodNo],
                ['status' => 'pending'],
            );

            foreach ($teamIds as $teamId) {
                MatchPeriodScore::query()->firstOrCreate(
                    ['match_period_id' => $period->id, 'team_id' => $teamId],
                    ['score' => 0],
                );
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Game>
     */
    private function gamesForForm(?GameMatch $match = null)
    {
        return Game::query()
            ->where(function ($query) use ($match): void {
                $query->where('is_active', true);

                if ($match?->game_id) {
                    $query->orWhere('id', $match->game_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function activeGame(int $gameId, ?GameMatch $match = null): Game
    {
        $game = Game::query()->findOrFail($gameId);

        if ($game->is_active) {
            return $game;
        }

        if ($match !== null && (int) $match->game_id === (int) $game->id) {
            return $game;
        }

        throw ValidationException::withMessages([
            'game_id' => 'Game ini nonaktif.',
        ]);
    }
}
