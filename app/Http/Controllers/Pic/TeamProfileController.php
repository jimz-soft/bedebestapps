<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\MatchRoster;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamProfileController extends Controller
{
    public function show(Request $request): View
    {
        $team = $request->user()->team()->with([
            'participants' => fn ($query) => $query->orderByDesc('is_captain')->orderByDesc('is_co_captain')->orderBy('name'),
        ])->firstOrFail();

        $matches = GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->whereHas('game', fn ($game) => $game->active()->where('requires_roster', true))
            ->whereHas('matchTeams', fn ($side) => $side->where('team_id', $team->id))
            ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
            ->orderByRaw('case when scheduled_at is null then 1 else 0 end')
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        $rosters = $matches->isEmpty()
            ? collect()
            : MatchRoster::query()
                ->with('participant')
                ->where('team_id', $team->id)
                ->whereIn('match_id', $matches->modelKeys())
                ->get()
                ->groupBy(fn (MatchRoster $row) => (int) $row->match_id);

        return view('pic.profil', [
            'team' => $team,
            'matches' => $matches,
            'rosters' => $rosters,
        ]);
    }
}
