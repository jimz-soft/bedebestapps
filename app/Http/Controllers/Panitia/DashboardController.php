<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Team;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('panitia.dashboard', [
            'teamCount' => Team::query()->count(),
            'gameCount' => Game::query()->active()->count(),
            'liveCount' => GameMatch::query()->where('status', 'live')->count(),
            'finishedCount' => GameMatch::query()->where('status', 'selesai')->count(),
        ]);
    }
}
