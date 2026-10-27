<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\MatchPeriod;
use App\Models\MatchPeriodScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScoreController extends Controller
{
    public function index(): View
    {
        return view('panitia.scores.index', [
            'matches' => GameMatch::query()
                ->with(['game', 'matchTeams.team'])
                ->orderByRaw("case status when 'live' then 0 when 'upcoming' then 1 else 2 end")
                ->latest('scheduled_at')
                ->get(),
        ]);
    }

    public function edit(GameMatch $gameMatch): View
    {
        $gameMatch->load(['game', 'matchTeams.team', 'periods.scores']);

        return view('panitia.scores.edit', ['match' => $gameMatch]);
    }

    public function update(Request $request, GameMatch $gameMatch): RedirectResponse
    {
        $match = $gameMatch;
        $match->load(['game', 'matchTeams.team', 'periods']);

        $payload = $request->validate([
            'status' => ['required', 'in:upcoming,live,selesai'],
            'periods' => ['nullable', 'array'],
            'periods.*.id' => ['required', 'integer'],
            'periods.*.status' => ['required', 'in:pending,running,done'],
            'periods.*.scores' => ['nullable', 'array'],
            'periods.*.scores.*' => ['nullable', 'integer', 'min:0', 'max:999'],
            'teams' => ['required', 'array'],
            'teams.*.points_awarded' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'teams.*.poin_personil' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'teams.*.bonus_pemenang' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'teams.*.bonus_keterangan' => ['nullable', 'string', 'max:255'],
            'teams.*.result' => ['nullable', 'in:win,loss,draw'],
        ]);

        $this->saveTeamPoints($match, $payload['teams']);

        if ($match->game->uses_period_score) {
            foreach ($payload['periods'] ?? [] as $periodPayload) {
                $period = $match->periods->firstWhere('id', (int) $periodPayload['id']);

                if ($period === null) {
                    continue;
                }

                $period->update(['status' => $periodPayload['status']]);

                foreach ($periodPayload['scores'] ?? [] as $teamId => $score) {
                    MatchPeriodScore::query()->updateOrCreate(
                        ['match_period_id' => $period->id, 'team_id' => (int) $teamId],
                        ['score' => (int) $score],
                    );
                }
            }
        }

        $this->refreshScoreCache($match);

        if ($request->input('intent') === 'finish') {
            $this->markFinished($match);

            return back()->with('status', 'Skor, poin, dan bonus sudah disimpan. Pertandingan ditandai selesai.');
        }

        $status = $payload['status'] === 'upcoming' ? 'live' : $payload['status'];
        $match->update(['status' => $status]);

        return back()->with('status', 'Skor dan poin sudah disimpan.');
    }

    public function finish(GameMatch $gameMatch): RedirectResponse
    {
        $match = $gameMatch;
        $match->load(['game', 'matchTeams', 'periods.scores']);
        $this->refreshScoreCache($match);
        $this->markFinished($match);

        return back()->with('status', 'Pertandingan ditandai selesai. Poin masuk leaderboard.');
    }

    public function addPeriod(GameMatch $gameMatch): RedirectResponse
    {
        $match = $gameMatch;
        $match->load(['game', 'matchTeams', 'periods']);

        if (! $match->game->uses_period_score) {
            return back()->withErrors(['periods' => 'Game ini tidak memakai skor babak.']);
        }

        $next = (int) $match->periods->max('period_no') + 1;
        $period = MatchPeriod::query()->create([
            'match_id' => $match->id,
            'period_no' => $next,
            'status' => 'pending',
        ]);

        foreach ($match->matchTeams as $side) {
            MatchPeriodScore::query()->create([
                'match_period_id' => $period->id,
                'team_id' => $side->team_id,
                'score' => 0,
            ]);
        }

        return back()->with('status', 'Babak tambahan sudah dibuat.');
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $teams
     */
    private function saveTeamPoints(GameMatch $match, array $teams): void
    {
        foreach ($match->matchTeams as $side) {
            $row = $teams[$side->id] ?? [];
            $bonus = (int) ($row['bonus_pemenang'] ?? 0);
            $note = trim((string) ($row['bonus_keterangan'] ?? ''));

            if ($bonus > 0 && $note === '') {
                throw ValidationException::withMessages([
                    'teams.'.$side->id.'.bonus_keterangan' => 'Isi keterangan bonus untuk '.$side->team->name.'.',
                ]);
            }

            $side->update([
                'points_awarded' => (int) ($row['points_awarded'] ?? 0),
                'poin_personil' => (int) ($row['poin_personil'] ?? 0),
                'bonus_pemenang' => $bonus,
                'bonus_keterangan' => $bonus > 0 ? $note : null,
                'result' => $row['result'] ?? null,
            ]);
        }
    }

    private function markFinished(GameMatch $match): void
    {
        $match->unsetRelation('matchTeams');
        $match->load(['game', 'matchTeams', 'periods.scores']);
        $this->applyResults($match);

        $match->update([
            'status' => 'selesai',
            'finished_at' => $match->finished_at ?? now(),
        ]);
    }

    private function refreshScoreCache(GameMatch $match): void
    {
        $match->unsetRelation('periods');
        $match->unsetRelation('matchTeams');
        $match->load(['game', 'matchTeams.team', 'periods.scores']);

        foreach ($match->matchTeams as $side) {
            if ($match->game->uses_period_score) {
                $scores = $match->periods->map(function (MatchPeriod $period) use ($side): int {
                    return (int) $period->scores->firstWhere('team_id', $side->team_id)?->score;
                });

                $side->score_cache = $match->game->score_mode === 'sets'
                    ? $this->setsWon($match, $side->team_id)
                    : $scores->sum();
            } else {
                $side->score_cache = (int) $side->points_awarded;
            }

            $side->save();
        }
    }

    private function setsWon(GameMatch $match, int $teamId): int
    {
        $won = 0;

        foreach ($match->periods as $period) {
            $own = (int) $period->scores->firstWhere('team_id', $teamId)?->score;
            $opp = (int) $period->scores->first(fn ($score) => (int) $score->team_id !== $teamId)?->score;

            if ($own > $opp) {
                $won++;
            }
        }

        return $won;
    }

    private function applyResults(GameMatch $match): void
    {
        $sides = $match->matchTeams;

        if ($sides->count() === 2) {
            $first = $sides[0];
            $second = $sides[1];
            $left = (int) $first->score_cache;
            $right = (int) $second->score_cache;

            if ($left === $right) {
                $first->update(['result' => 'draw']);
                $second->update(['result' => 'draw']);

                return;
            }

            $first->update(['result' => $left > $right ? 'win' : 'loss']);
            $second->update(['result' => $right > $left ? 'win' : 'loss']);

            return;
        }

        $top = (int) $sides->max('points_awarded');

        foreach ($sides as $side) {
            $side->update([
                'result' => (int) $side->points_awarded === $top && $top > 0 ? 'win' : 'loss',
            ]);
        }
    }
}
