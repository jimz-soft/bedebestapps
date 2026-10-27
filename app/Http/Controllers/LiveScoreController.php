<?php

namespace App\Http\Controllers;

use App\Support\LiveBoard;
use Illuminate\Http\JsonResponse;

class LiveScoreController extends Controller
{
    public function __invoke(LiveBoard $board): JsonResponse
    {
        return response()
            ->json($board->toArray())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
