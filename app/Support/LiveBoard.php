<?php

namespace App\Support;

use App\Models\GameMatch;
use App\Models\MatchTeam;
use App\Models\Team;
use Illuminate\Support\Collection;

class LiveBoard
{
    public function liveMatch(): ?GameMatch
    {
        return GameMatch::query()
            ->with(['game', 'matchTeams.team'])
            ->where('status', 'live')
            ->orderByDesc('scheduled_at')
            ->first();
    }

    /**
     * @return Collection<int, array{team: Team, poin_games: int, poin_personil: int, total: int}>
     */
    public function standings(): Collection
    {
        $teams = Team::query()->orderBy('sort_order')->get();
        $points = MatchTeam::query()
            ->whereHas('match', fn ($query) => $query->where('status', 'selesai'))
            ->selectRaw('team_id, SUM(points_awarded + bonus_pemenang) as poin_games, SUM(poin_personil) as poin_personil')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        return $teams
            ->map(function (Team $team) use ($points): array {
                $row = $points->get($team->id);
                $poinGames = (int) ($row->poin_games ?? 0);
                $poinPersonil = (int) ($row->poin_personil ?? 0);

                return [
                    'team' => $team,
                    'poin_games' => $poinGames,
                    'poin_personil' => $poinPersonil,
                    'total' => $poinGames + $poinPersonil,
                ];
            })
            ->sort(function (array $left, array $right): int {
                return [$right['total'], $right['poin_games'], $right['poin_personil']]
                    <=> [$left['total'], $left['poin_games'], $left['poin_personil']];
            })
            ->values();
    }

    /**
     * @return array{live: ?array<string, mixed>, standings: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        $match = $this->liveMatch();

        return [
            'live' => $match === null ? null : [
                'id' => $match->id,
                'game' => $match->game->name,
                'round' => $match->round_label,
                'sides' => $match->matchTeams->map(fn (MatchTeam $side): array => [
                    'code' => $side->team->code,
                    'name' => $side->team->name,
                    'color' => $side->team->color,
                    'logo' => $side->team->logoUrl(),
                    'score' => (int) ($side->score_cache ?? 0),
                ])->values()->all(),
            ],
            'standings' => $this->standings()->map(fn (array $row): array => [
                'name' => $row['team']->name,
                'total' => $row['total'],
            ])->take(4)->values()->all(),
        ];
    }
}
