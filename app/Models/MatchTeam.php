<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'match_id',
    'team_id',
    'sort_order',
    'points_awarded',
    'poin_personil',
    'bonus_pemenang',
    'bonus_keterangan',
    'result',
    'score_cache',
])]
class MatchTeam extends Model
{
    /**
     * @return BelongsTo<GameMatch, $this>
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function isWinner(): bool
    {
        return $this->result === 'win';
    }
}
