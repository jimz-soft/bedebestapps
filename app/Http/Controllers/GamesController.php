<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Setting;
use Illuminate\View\View;

class GamesController extends Controller
{
    public function __invoke(): View
    {
        return view('games', [
            'setting' => Setting::query()->first(),
            'games' => Game::query()->active()->orderBy('name')->get(),
        ]);
    }
}
