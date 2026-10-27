<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRoster;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamProfileController extends Controller
{
    public function show(Request $request): View
    {
        $team = $request->user()->team()->with([
            'participants' => fn ($query) => $query->orderByDesc('is_captain')->orderBy('name'),
        ])->firstOrFail();

        $games = Game::query()->active()->where('requires_roster', true)->orderBy('id')->get();
        $rosters = GameRoster::query()
            ->with('participant')
            ->where('team_id', $team->id)
            ->whereIn('game_id', $games->modelKeys())
            ->get()
            ->groupBy(fn (GameRoster $row) => (int) $row->game_id);

        return view('pic.profil', [
            'team' => $team,
            'games' => $games,
            'rosters' => $rosters,
        ]);
    }
}
