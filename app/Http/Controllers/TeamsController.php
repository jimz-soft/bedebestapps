<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\GameRoster;
use App\Models\Setting;
use App\Models\Team;
use App\Support\LiveBoard;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeamsController extends Controller
{
    public function index(LiveBoard $board): View
    {
        $ranked = $this->rankedStandings($board);

        $groups = Team::query()
            ->withCount([
                'participants',
                'participants as captains_count' => fn ($query) => $query->where('is_captain', true),
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function (Team $team) use ($ranked): array {
                $row = $ranked->get($team->id);

                return [
                    'team' => $team,
                    'rank' => (int) ($row['rank'] ?? 0),
                    'poin_games' => (int) ($row['poin_games'] ?? 0),
                    'poin_personil' => (int) ($row['poin_personil'] ?? 0),
                    'total' => (int) ($row['total'] ?? 0),
                    'peserta' => (int) $team->participants_count,
                    'kapten' => (int) $team->captains_count,
                ];
            });

        return view('tim', [
            'setting' => Setting::query()->first(),
            'groups' => $groups,
            'heading' => $this->groupHeading($groups->count()),
        ]);
    }

    public function show(Team $team, LiveBoard $board): View
    {
        $team->load([
            'participants' => fn ($query) => $query->orderByDesc('is_captain')->orderBy('name'),
        ]);

        $row = $this->rankedStandings($board)->get($team->id);
        $rosterGames = Game::query()->active()->where('requires_roster', true)->orderBy('name')->get();
        $rosters = $rosterGames->isEmpty()
            ? collect()
            : GameRoster::query()
                ->with('participant')
                ->where('team_id', $team->id)
                ->whereIn('game_id', $rosterGames->modelKeys())
                ->get()
                ->sortBy(fn (GameRoster $item) => [
                    $item->participant?->is_captain ? 0 : 1,
                    strtolower((string) $item->participant?->name),
                ])
                ->groupBy(fn (GameRoster $item) => (int) $item->game_id);

        $matches = GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->whereHas('matchTeams', fn ($query) => $query->where('team_id', $team->id))
            ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
            ->orderByRaw('case when scheduled_at is null then 1 else 0 end')
            ->orderBy('scheduled_at')
            ->get();

        return view('tim-show', [
            'setting' => Setting::query()->first(),
            'team' => $team,
            'rank' => (int) ($row['rank'] ?? 0),
            'poin_games' => (int) ($row['poin_games'] ?? 0),
            'poin_personil' => (int) ($row['poin_personil'] ?? 0),
            'total' => (int) ($row['total'] ?? 0),
            'captains' => $team->participants->where('is_captain', true)->values(),
            'rosterGames' => $rosterGames,
            'rosters' => $rosters,
            'matches' => $matches,
        ]);
    }

    /**
     * @return Collection<int, array{team: Team, poin_games: int, poin_personil: int, total: int, rank: int}>
     */
    private function rankedStandings(LiveBoard $board): Collection
    {
        return $board->standings()
            ->values()
            ->map(function (array $row, int $index): array {
                $row['rank'] = $index + 1;

                return $row;
            })
            ->keyBy(fn (array $row) => $row['team']->id);
    }

    private function groupHeading(int $count): string
    {
        $words = [
            0 => 'BELUM ADA',
            1 => 'SATU',
            2 => 'DUA',
            3 => 'TIGA',
            4 => 'EMPAT',
            5 => 'LIMA',
            6 => 'ENAM',
            7 => 'TUJUH',
            8 => 'DELAPAN',
            9 => 'SEMBILAN',
            10 => 'SEPULUH',
        ];

        $word = $words[$count] ?? (string) $count;

        return $word.' GRUP';
    }
}
