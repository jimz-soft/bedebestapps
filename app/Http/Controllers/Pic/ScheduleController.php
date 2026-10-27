<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $team = $request->user()->team()->firstOrFail();

        $matches = GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
            ->orderBy('scheduled_at')
            ->get();

        return view('pic.jadwal', [
            'team' => $team,
            'matches' => $matches,
        ]);
    }
}
