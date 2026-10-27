<?php

namespace App\Models;

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
