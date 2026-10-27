<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __invoke(string $page): View
    {
        $titles = [
            'jadwal' => 'Jadwal',
            'games' => 'Games',
            'tim' => 'Tim',
            'bagan' => 'Bagan',
            'leaderboard' => 'Leaderboard',
        ];

        abort_unless(isset($titles[$page]), 404);

        return view('page', [
            'setting' => Setting::query()->first(),
            'title' => $titles[$page],
            'active' => $page,
        ]);
    }
}
