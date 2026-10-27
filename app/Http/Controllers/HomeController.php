<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Setting;
use App\Models\Team;
use App\Support\LiveBoard;
use App\Support\MatchCountdown;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(LiveBoard $board, MatchCountdown $countdown): View
    {
        $setting = Setting::query()->first();

        return view('home', [
            'setting' => $setting,
            'teams' => Team::query()->orderBy('sort_order')->get(),
            'games' => Game::query()->active()->orderBy('name')->get(),
            'liveMatch' => $board->liveMatch(),
            'upcomingMatches' => GameMatch::query()
                ->with(['game', 'matchTeams.team'])
                ->where('status', 'upcoming')
                ->orderBy('scheduled_at')
                ->limit(3)
                ->get(),
            'standings' => $board->standings(),
            'countdown' => $countdown->current(),
        ]);
    }
}
