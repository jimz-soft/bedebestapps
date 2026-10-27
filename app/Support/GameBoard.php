<?php

namespace App\Support;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\MatchTeam;
use App\Models\Team;
use Illuminate\Support\Collection;

class GameBoard
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $teams = Team::query()->orderBy('sort_order')->get();

        return Game::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Game $game): array => $this->forGame($game, $teams))
            ->all();
    }

    /**
     * @param  Collection<int, Team>  $teams
     * @return array<string, mixed>
     */
    public function forGame(Game $game, Collection $teams): array
    {
        $matches = GameMatch::query()
            ->with(['game', 'matchTeams.team', 'periods.scores'])
            ->where('game_id', $game->id)
            ->orderByRaw('case when scheduled_at is null then 1 else 0 end')
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        return [
            'game' => $game,
            'caption' => $this->caption($game),
            'liveCount' => $matches->where('status', 'live')->count(),
            'matches' => $matches,
            'standings' => $this->standings($game, $teams, $matches),
            'rounds' => $game->format === 'head_to_head' ? $this->knockoutRounds($matches) : collect(),
            'matrix' => $game->format === 'round_robin' ? $this->matrix($teams, $matches) : null,
        ];
    }

    private function caption(Game $game): string
    {
        return match ($game->format) {
            'head_to_head' => 'sistem gugur',
            'round_robin' => 'round robin',
            'heat' => 'lomba bersama',
            default => (string) $game->format,
        };
    }

    /**
     * @param  Collection<int, Team>  $teams
     * @param  Collection<int, GameMatch>  $matches
     * @return Collection<int, array<string, mixed>>
     */
    private function standings(Game $game, Collection $teams, Collection $matches): Collection
    {
        $finished = $matches->where('status', 'selesai')->values();

        return $teams
            ->map(function (Team $team) use ($game, $finished): array {
                $playedMatches = $finished->filter(
                    fn (GameMatch $match) => $match->matchTeams->contains('team_id', $team->id)
                );
                $sides = $playedMatches->map(
                    fn (GameMatch $match) => $match->matchTeams->firstWhere('team_id', $team->id)
                )->filter();

                $setsFor = 0;
                $setsAgainst = 0;

                if ($game->score_mode === 'sets') {
                    foreach ($playedMatches as $match) {
                        $self = $match->matchTeams->firstWhere('team_id', $team->id);
                        $opponent = $match->matchTeams->first(fn (MatchTeam $side) => $side->team_id !== $team->id);
                        $setsFor += (int) ($self?->score_cache ?? 0);
                        $setsAgainst += (int) ($opponent?->score_cache ?? 0);
                    }
                }

                $poinGames = $sides->sum(fn (MatchTeam $side) => (int) $side->points_awarded + (int) $side->bonus_pemenang);
                $poinPersonil = $sides->sum(fn (MatchTeam $side) => (int) $side->poin_personil);

                return [
                    'team' => $team,
                    'played' => $playedMatches->count(),
                    'wins' => $sides->where('result', 'win')->count(),
                    'losses' => $sides->where('result', 'loss')->count(),
                    'sets_for' => $setsFor,
                    'sets_against' => $setsAgainst,
                    'poin_games' => $poinGames,
                    'poin_personil' => $poinPersonil,
                    'total' => $poinGames + $poinPersonil,
                ];
            })
            ->sort(function (array $left, array $right): int {
                return [
                    $right['total'],
                    $right['poin_games'],
                    $right['wins'],
                    $right['sets_for'] - $right['sets_against'],
                ] <=> [
                    $left['total'],
                    $left['poin_games'],
                    $left['wins'],
                    $left['sets_for'] - $left['sets_against'],
                ];
            })
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
    }

    /**
     * @param  Collection<int, GameMatch>  $matches
     * @return Collection<int, array{label: string, featured: bool, matches: Collection<int, GameMatch>}>
     */
    private function knockoutRounds(Collection $matches): Collection
    {
        return $matches
            ->groupBy(fn (GameMatch $match) => $match->round_label ?: 'Babak')
            ->map(function (Collection $items, string $label): array {
                $lower = mb_strtolower($label);

                return [
                    'label' => $label,
                    'featured' => str_contains($lower, 'final') && ! str_contains($lower, 'semi'),
                    'sort' => $this->roundSort($lower),
                    'matches' => $items->values(),
                ];
            })
            ->sortBy(fn (array $round) => [
                $round['sort'],
                $round['matches']->first()?->scheduled_at?->timestamp ?? 0,
            ])
            ->values();
    }

    private function roundSort(string $label): int
    {
        if (str_contains($label, 'quarter') || str_contains($label, 'perempat')) {
            return 20;
        }

        if (str_contains($label, 'semi')) {
            return 40;
        }

        if (str_contains($label, 'final')) {
            return 80;
        }

        if (str_contains($label, 'penyisihan')) {
            return 10;
        }

        return 50;
    }

    /**
     * @param  Collection<int, Team>  $teams
     * @param  Collection<int, GameMatch>  $matches
     * @return array{teams: Collection<int, Team>, cells: array<int, array<int, array<string, mixed>>>}
     */
    private function matrix(Collection $teams, Collection $matches): array
    {
        $cells = [];

        foreach ($teams as $row) {
            foreach ($teams as $column) {
                $cells[$row->id][$column->id] = $this->matrixCell($row, $column, $matches);
            }
        }

        return [
            'teams' => $teams,
            'cells' => $cells,
        ];
    }

    /**
     * @param  Collection<int, GameMatch>  $matches
     * @return array{type: string, text: string, won: bool|null, live: bool}
     */
    private function matrixCell(Team $row, Team $column, Collection $matches): array
    {
        if ($row->id === $column->id) {
            return ['type' => 'self', 'text' => '—', 'won' => null, 'live' => false];
        }

        $match = $matches->first(function (GameMatch $match) use ($row, $column): bool {
            $ids = $match->matchTeams->pluck('team_id');

            return $ids->contains($row->id) && $ids->contains($column->id);
        });

        if ($match === null) {
            return ['type' => 'empty', 'text' => '—', 'won' => null, 'live' => false];
        }

        $self = $match->matchTeams->firstWhere('team_id', $row->id);
        $opponent = $match->matchTeams->firstWhere('team_id', $column->id);
        $live = $match->status === 'live';

        if (! in_array($match->status, ['live', 'selesai'], true)) {
            return [
                'type' => 'date',
                'text' => $match->shortDate() ?: ($match->round_label ?: '—'),
                'won' => null,
                'live' => false,
            ];
        }

        $left = (int) ($self?->score_cache ?? 0);
        $right = (int) ($opponent?->score_cache ?? 0);

        return [
            'type' => 'score',
            'text' => $left.'–'.$right,
            'won' => $left === $right ? null : $left > $right,
            'live' => $live,
        ];
    }
}
