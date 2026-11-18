<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\MatchRoster;
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
                'participants as co_captains_count' => fn ($query) => $query->where('is_co_captain', true),
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
                    'co_kapten' => (int) $team->co_captains_count,
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
            'participants' => fn ($query) => $query->orderByDesc('is_captain')->orderByDesc('is_co_captain')->orderBy('name'),
        ]);

        $row = $this->rankedStandings($board)->get($team->id);

        $rosterMatches = GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->whereHas('game', fn ($game) => $game->active()->where('requires_roster', true))
            ->whereHas('matchTeams', fn ($side) => $side->where('team_id', $team->id))
            ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
            ->orderByRaw('case when scheduled_at is null then 1 else 0 end')
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        $rosters = $rosterMatches->isEmpty()
            ? collect()
            : MatchRoster::query()
                ->with('participant')
                ->where('team_id', $team->id)
                ->whereIn('match_id', $rosterMatches->modelKeys())
                ->get()
                ->sortBy(fn (MatchRoster $item) => [
                    $item->participant?->is_captain ? 0 : ($item->participant?->is_co_captain ? 1 : 2),
                    strtolower((string) $item->participant?->name),
                ])
                ->groupBy(fn (MatchRoster $item) => (int) $item->match_id);

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
            'captain' => $team->participants->firstWhere('is_captain', true),
            'coCaptain' => $team->participants->firstWhere('is_co_captain', true),
            'rosterMatches' => $rosterMatches,
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
