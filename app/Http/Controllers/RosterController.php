<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\GameRoster;
use App\Models\Participant;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RosterController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->actor($request);
        [$games, $teams, $game, $team] = $this->context($request, $user);

        $participants = Participant::query()
            ->where('team_id', $team->id)
            ->orderByDesc('is_captain')
            ->orderBy('name')
            ->get();

        $selectedIds = GameRoster::query()
            ->where('game_id', $game->id)
            ->where('team_id', $team->id)
            ->pluck('participant_id')
            ->all();

        return view('rosters.edit', [
            'layout' => $user->isAdministrator() ? 'layouts.panitia' : 'layouts.tim',
            'formAction' => $user->isAdministrator() ? route('panitia.rosters.update') : route('pic.rosters.update'),
            'games' => $games,
            'teams' => $teams,
            'game' => $game,
            'team' => $team,
            'participants' => $participants,
            'selectedIds' => $selectedIds,
            'locked' => $this->isLocked($game, $team, $user),
            'canPickTeam' => $user->isAdministrator(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->actor($request);
        [, , $game, $team] = $this->context($request, $user);

        if ($this->isLocked($game, $team, $user)) {
            throw ValidationException::withMessages([
                'participant_ids' => 'Roster sudah terkunci karena semua pertandingan cabang ini untuk grup tersebut sudah selesai.',
            ]);
        }

        if (! $game->requires_roster) {
            throw ValidationException::withMessages([
                'game_id' => 'Game ini tidak memakai roster.',
            ]);
        }

        $ids = collect($request->input('participant_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validCount = Participant::query()
            ->where('team_id', $team->id)
            ->whereIn('id', $ids)
            ->count();

        if ($validCount !== $ids->count()) {
            throw ValidationException::withMessages([
                'participant_ids' => 'Roster hanya boleh berisi peserta grup ini.',
            ]);
        }

        $existing = GameRoster::query()
            ->where('game_id', $game->id)
            ->where('team_id', $team->id);

        if ($ids->isEmpty()) {
            $existing->delete();
        } else {
            $existing->whereNotIn('participant_id', $ids->all())->delete();
        }

        foreach ($ids as $participantId) {
            GameRoster::query()->firstOrCreate([
                'game_id' => $game->id,
                'team_id' => $team->id,
                'participant_id' => $participantId,
            ]);
        }

        $index = $user->isAdministrator() ? 'panitia.rosters.edit' : 'pic.rosters.edit';

        return redirect()
            ->route($index, ['game_id' => $game->id, 'team_id' => $team->id])
            ->with('status', 'Roster '.$team->name.' untuk '.$game->name.' sudah disimpan.');
    }

    private function actor(Request $request): User
    {
        $user = $request->user();

        if ($user === null || $user->is_active !== true) {
            abort(403);
        }

        if ($user->isAdministrator()) {
            return $user;
        }

        if ($user->isUser() && $user->team_id) {
            return $user;
        }

        abort(403);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, Game>, 1: \Illuminate\Support\Collection<int, Team>, 2: Game, 3: Team}
     */
    private function context(Request $request, User $user): array
    {
        $games = Game::query()->active()->where('requires_roster', true)->orderBy('id')->get();
        $teams = $user->isAdministrator()
            ? Team::query()->orderBy('sort_order')->get()
            : Team::query()->whereKey($user->team_id)->get();

        if ($games->isEmpty() || $teams->isEmpty()) {
            abort(404, 'Belum ada game roster atau grup.');
        }

        $gameId = (int) $request->input('game_id', $games->first()->id);
        $teamId = $user->isAdministrator()
            ? (int) $request->input('team_id', $teams->first()->id)
            : (int) $user->team_id;

        $game = $games->firstWhere('id', $gameId);
        $team = $teams->firstWhere('id', $teamId) ?? $teams->first();

        if ($game === null) {
            if ($request->isMethod('PUT') || $request->isMethod('POST')) {
                throw ValidationException::withMessages([
                    'game_id' => 'Game ini tidak memakai roster.',
                ]);
            }

            $game = $games->first();
        }

        return [$games, $teams, $game, $team];
    }

    private function isLocked(Game $game, Team $team, User $user): bool
    {
        if ($user->isAdministrator()) {
            return false;
        }

        $matches = fn () => GameMatch::query()
            ->where('game_id', $game->id)
            ->whereHas('matchTeams', fn ($match) => $match->where('team_id', $team->id));

        if ($matches()->whereIn('status', ['upcoming', 'live'])->exists()) {
            return false;
        }

        return $matches()->where('status', 'selesai')->exists();
    }
}
