<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchRoster;
use App\Models\Participant;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RosterController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->actor($request);
        $allMatches = $this->availableMatches($user);
        $layout = $user->isAdministrator() ? 'layouts.panitia' : 'layouts.tim';
        $formAction = $user->isAdministrator() ? route('panitia.rosters.update') : route('pic.rosters.update');
        $canPickTeam = $user->isAdministrator();

        $games = $this->gamesFromMatches($allMatches);

        if ($games->isEmpty()) {
            return view('rosters.edit', [
                'layout' => $layout,
                'formAction' => $formAction,
                'games' => $games,
                'matches' => collect(),
                'match' => null,
                'game' => null,
                'team' => $canPickTeam ? null : $user->team,
                'teams' => collect(),
                'participants' => collect(),
                'selectedIds' => [],
                'locked' => false,
                'lockReason' => null,
                'deadlineCountdown' => null,
                'canPickTeam' => $canPickTeam,
            ]);
        }

        [$game, $match, $team, $matches] = $this->resolveContext($request, $user, $allMatches, $games);

        $participants = Participant::query()
            ->where('team_id', $team->id)
            ->orderByDesc('is_captain')
            ->orderByDesc('is_co_captain')
            ->orderBy('name')
            ->get();

        $selectedIds = MatchRoster::query()
            ->where('match_id', $match->id)
            ->where('team_id', $team->id)
            ->pluck('participant_id')
            ->all();

        return view('rosters.edit', [
            'layout' => $layout,
            'formAction' => $formAction,
            'games' => $games,
            'matches' => $matches,
            'match' => $match,
            'game' => $game,
            'team' => $team,
            'teams' => $match->matchTeams->pluck('team')->filter()->values(),
            'participants' => $participants,
            'selectedIds' => $selectedIds,
            'locked' => $this->isLocked($match, $user),
            'lockReason' => $this->lockReason($match, $user),
            'deadlineCountdown' => (! $canPickTeam && $match->status !== 'selesai')
                ? $match->rosterDeadlineCountdown()
                : null,
            'canPickTeam' => $canPickTeam,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->actor($request);
        $allMatches = $this->availableMatches($user);
        $games = $this->gamesFromMatches($allMatches);

        if ($games->isEmpty()) {
            throw ValidationException::withMessages([
                'match_id' => 'Belum ada jadwal pertandingan yang memakai roster.',
            ]);
        }

        [, $match, $team] = $this->resolveContext($request, $user, $allMatches, $games);
        $game = $match->game;

        if ($this->isLocked($match, $user)) {
            throw ValidationException::withMessages([
                'participant_ids' => $this->lockMessage($match, $user),
            ]);
        }

        if (! $game?->requires_roster) {
            throw ValidationException::withMessages([
                'match_id' => 'Pertandingan ini tidak memakai roster.',
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

        $min = (int) ($game->roster_min ?? 0);
        $max = (int) ($game->roster_max ?? 50);
        $count = $ids->count();

        if ($count < $min || $count > $max) {
            throw ValidationException::withMessages([
                'participant_ids' => 'Roster '.$game->name.' harus '.$min.'–'.$max.' pemain. Saat ini dipilih '.$count.'.',
            ]);
        }

        $existing = MatchRoster::query()
            ->where('match_id', $match->id)
            ->where('team_id', $team->id);

        if ($ids->isEmpty()) {
            $existing->delete();
        } else {
            $existing->whereNotIn('participant_id', $ids->all())->delete();
        }

        foreach ($ids as $participantId) {
            MatchRoster::query()->firstOrCreate([
                'match_id' => $match->id,
                'team_id' => $team->id,
                'participant_id' => $participantId,
            ]);
        }

        $index = $user->isAdministrator() ? 'panitia.rosters.edit' : 'pic.rosters.edit';

        return redirect()
            ->route($index, [
                'game_id' => $match->game_id,
                'match_id' => $match->id,
                'team_id' => $team->id,
            ])
            ->with('status', 'Roster '.$team->name.' untuk '.$match->rosterLabel().' sudah disimpan.');
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
     * @param  Collection<int, GameMatch>  $allMatches
     * @param  Collection<int, Game>  $games
     * @return array{0: Game, 1: GameMatch, 2: Team, 3: Collection<int, GameMatch>}
     */
    private function resolveContext(Request $request, User $user, Collection $allMatches, Collection $games): array
    {
        $requestedMatch = null;
        if ($request->filled('match_id')) {
            $requestedMatch = $allMatches->firstWhere('id', (int) $request->input('match_id'));
        }

        $gameId = (int) $request->input(
            'game_id',
            $requestedMatch?->game_id ?? $games->first()->id
        );

        $game = $games->firstWhere('id', $gameId);

        if ($game === null) {
            if ($request->isMethod('PUT') || $request->isMethod('POST')) {
                throw ValidationException::withMessages([
                    'match_id' => 'Pertandingan ini tidak memakai roster.',
                ]);
            }

            $game = $games->first();
        }

        $matches = $allMatches->where('game_id', $game->id)->values();

        if ($matches->isEmpty()) {
            abort(404, 'Belum ada jadwal untuk game ini.');
        }

        $match = $matches->firstWhere('id', (int) $request->input('match_id'));

        if ($match === null) {
            if (($request->isMethod('PUT') || $request->isMethod('POST')) && $request->filled('match_id')) {
                throw ValidationException::withMessages([
                    'match_id' => 'Pertandingan ini tidak memakai roster.',
                ]);
            }

            $match = $matches->first();
        }

        $match->loadMissing(['game', 'matchTeams.team']);

        $teamOptions = $match->matchTeams->pluck('team')->filter()->values();

        if ($user->isAdministrator()) {
            $teamId = (int) $request->input('team_id', $teamOptions->first()?->id);
            $team = $teamOptions->firstWhere('id', $teamId) ?? $teamOptions->first();
        } else {
            $team = $teamOptions->firstWhere('id', $user->team_id);
        }

        if ($team === null) {
            abort(404, 'Grup tidak ikut di pertandingan ini.');
        }

        return [$game, $match, $team, $matches];
    }

    /**
     * @param  Collection<int, GameMatch>  $matches
     * @return Collection<int, Game>
     */
    private function gamesFromMatches(Collection $matches): Collection
    {
        return $matches
            ->map(fn (GameMatch $match) => $match->game)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    /**
     * @return Collection<int, GameMatch>
     */
    private function availableMatches(User $user): Collection
    {
        $query = GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->whereHas('game', fn ($game) => $game->active()->where('requires_roster', true))
            ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
            ->orderByRaw('case when scheduled_at is null then 1 else 0 end')
            ->orderBy('scheduled_at')
            ->orderBy('id');

        if (! $user->isAdministrator()) {
            $query->whereHas('matchTeams', fn ($side) => $side->where('team_id', $user->team_id));
        }

        return $query->get();
    }

    private function isLocked(GameMatch $match, User $user): bool
    {
        if ($user->isAdministrator()) {
            return false;
        }

        return $match->rosterInputClosedForUsers();
    }

    private function lockReason(GameMatch $match, User $user): ?string
    {
        if ($user->isAdministrator() || ! $this->isLocked($match, $user)) {
            return null;
        }

        if ($match->status === 'selesai') {
            return 'selesai';
        }

        return 'deadline';
    }

    private function lockMessage(GameMatch $match, User $user): string
    {
        return match ($this->lockReason($match, $user)) {
            'deadline' => 'Roster sudah terkunci. Batas input adalah sehari sebelum pertandingan pukul 23:59:59.',
            default => 'Roster sudah terkunci karena pertandingan ini sudah selesai.',
        };
    }
}
