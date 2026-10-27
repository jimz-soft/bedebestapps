<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __invoke(Request $request): View
    {
        $gameSlug = $request->string('game')->toString();
        $status = $request->string('status')->toString();
        $status = in_array($status, ['upcoming', 'live', 'selesai'], true) ? $status : null;
        $game = $gameSlug !== ''
            ? Game::query()->where('slug', $gameSlug)->first()
            : null;

        $matches = GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->when($game, fn ($query) => $query->where('game_id', $game->id))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw('case when scheduled_at is null then 1 else 0 end')
            ->orderBy('scheduled_at')
            ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
            ->get();

        $days = $matches
            ->groupBy(fn (GameMatch $match) => $match->scheduled_at?->toDateString() ?? 'tanpa-tanggal')
            ->map(fn ($items, $key) => [
                'label' => $key === 'tanpa-tanggal'
                    ? 'Belum dijadwalkan'
                    : $this->indonesianDate($items->first()->scheduled_at),
                'matches' => $items,
            ]);

        return view('jadwal', [
            'setting' => Setting::query()->first(),
            'games' => Game::query()->active()->orderBy('name')->get(),
            'days' => $days,
            'selectedGame' => $game,
            'selectedStatus' => $status,
        ]);
    }

    private function indonesianDate(Carbon $date): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        return $days[$date->dayOfWeek].', '.$date->format('j').' '.$months[$date->month].' '.$date->format('Y');
    }
}
