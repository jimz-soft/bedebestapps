<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['match_period_id', 'team_id', 'score'])]
class MatchPeriodScore extends Model
{
    /**
     * @return BelongsTo<MatchPeriod, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(MatchPeriod::class, 'match_period_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
