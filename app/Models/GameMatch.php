<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'game_id',
    'round_label',
    'scheduled_at',
    'location',
    'status',
    'banner_path',
    'notes',
    'bracket_node_id',
    'finished_at',
])]
class GameMatch extends Model
{
    protected $table = 'matches';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return HasMany<MatchTeam, $this>
     */
    public function matchTeams(): HasMany
    {
        return $this->hasMany(MatchTeam::class, 'match_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<MatchPeriod, $this>
     */
    public function periods(): HasMany
    {
        return $this->hasMany(MatchPeriod::class, 'match_id');
    }

    /**
     * @return HasMany<MatchRoster, $this>
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(MatchRoster::class, 'match_id');
    }

    public function rosterLabel(): string
    {
        $this->loadMissing(['game', 'matchTeams.team']);
        $parts = array_filter([
            $this->game?->name,
            $this->scheduleLabel(),
        ]);

        return implode(' · ', $parts);
    }

    public function scheduleLabel(): string
    {
        $this->loadMissing(['matchTeams.team']);
        $sides = $this->matchTeams->pluck('team.name')->filter()->join(' vs ');
        $parts = array_filter([
            $this->round_label,
            $sides !== '' ? $sides : null,
            $this->timeLocation() ?: null,
        ]);

        return implode(' · ', $parts) ?: ('Match #'.$this->id);
    }

    /**
     * Batas input roster untuk role user: sehari sebelum tanggal pertandingan, pukul 23:59:59.
     */
    public function rosterInputDeadline(): ?CarbonInterface
    {
        if ($this->scheduled_at === null) {
            return null;
        }

        return $this->scheduled_at
            ->copy()
            ->timezone(config('app.timezone'))
            ->subDay()
            ->setTime(23, 59, 59);
    }

    public function rosterInputClosedForUsers(): bool
    {
        if ($this->status === 'selesai') {
            return true;
        }

        $deadline = $this->rosterInputDeadline();

        return $deadline !== null && now()->greaterThan($deadline);
    }

    /**
     * @return array{days: string, hours: string, minutes: string, seconds: string, remaining: int, expired: bool, label: string}|null
     */
    public function rosterDeadlineCountdown(): ?array
    {
        $deadline = $this->rosterInputDeadline();

        if ($deadline === null) {
            return null;
        }

        $remaining = max(0, $deadline->getTimestamp() - now()->getTimestamp());
        $expired = $remaining <= 0;
        $diff = now()->diff($deadline);

        return [
            'days' => str_pad((string) ($expired ? 0 : $diff->days), 2, '0', STR_PAD_LEFT),
            'hours' => str_pad((string) ($expired ? 0 : $diff->h), 2, '0', STR_PAD_LEFT),
            'minutes' => str_pad((string) ($expired ? 0 : $diff->i), 2, '0', STR_PAD_LEFT),
            'seconds' => str_pad((string) ($expired ? 0 : $diff->s), 2, '0', STR_PAD_LEFT),
            'remaining' => $remaining,
            'expired' => $expired,
            'label' => 'Batas input roster · '.$deadline->timezone(config('app.timezone'))->format('d M Y H:i:s'),
        ];
    }

    public function isDuel(): bool
    {
        return $this->matchTeams->count() === 2 && $this->game?->format !== 'heat';
    }

    public function scheduleStamp(): string
    {
        if ($this->scheduled_at === null) {
            return $this->location ?: '';
        }

        $stamp = $this->scheduled_at->format('d').' '.$this->monthName();

        return $this->location ? $stamp.' · '.$this->location : $stamp;
    }

    public function shortDate(): string
    {
        if ($this->scheduled_at === null) {
            return '';
        }

        return $this->scheduled_at->format('j').' '.$this->monthName();
    }

    public function timeLocation(): string
    {
        if ($this->scheduled_at === null) {
            return $this->location ?: '';
        }

        $time = $this->scheduled_at->format('H:i');

        if ($this->location) {
            return $time.' · '.$this->location;
        }

        return trim($this->shortDate().' '.$time);
    }

    public function periodLine(): ?string
    {
        $this->loadMissing(['periods.scores', 'matchTeams']);

        if ($this->periods->isEmpty() || ! $this->isDuel()) {
            return null;
        }

        $homeId = (int) $this->matchTeams[0]->team_id;
        $awayId = (int) $this->matchTeams[1]->team_id;
        $parts = [];

        foreach ($this->periods->sortBy('period_no') as $period) {
            $home = (int) $period->scores->firstWhere('team_id', $homeId)?->score;
            $away = (int) $period->scores->firstWhere('team_id', $awayId)?->score;

            if ($period->status === 'pending' && $home === 0 && $away === 0) {
                continue;
            }

            $parts[] = $home.'-'.$away;
        }

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function monthName(): string
    {
        $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

        return $months[$this->scheduled_at->month];
    }
}
