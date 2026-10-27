<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\LiveBoard;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __invoke(LiveBoard $board): View
    {
        $standings = $board->standings()
            ->values()
            ->map(function (array $row, int $index): array {
                $rank = $index + 1;
                $row['rank'] = $rank;
                $row['rank_color'] = match ($rank) {
                    1 => 'text-brand-gold',
                    2 => 'text-[#c0c7d1]',
                    3 => 'text-[#cd7f32]',
                    default => 'text-white',
                };

                return $row;
            });

        return view('leaderboard', [
            'setting' => Setting::query()->first(),
            'standings' => $standings,
        ]);
    }
}
