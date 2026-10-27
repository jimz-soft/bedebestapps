<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\GameBoard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BaganController extends Controller
{
    public function __invoke(Request $request, GameBoard $board): View
    {
        $boards = $board->all();
        $slugs = collect($boards)->map(fn (array $row) => $row['game']->slug);
        $requested = $request->string('game')->toString();
        $selectedSlug = $slugs->contains($requested) ? $requested : $slugs->first();

        return view('bagan', [
            'setting' => Setting::query()->first(),
            'boards' => $boards,
            'selectedSlug' => $selectedSlug,
        ]);
    }
}
