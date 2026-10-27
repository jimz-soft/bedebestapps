<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['match_id', 'period_no', 'status'])]
class MatchPeriod extends Model
{
    /**
     * @return BelongsTo<GameMatch, $this>
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    /**
     * @return HasMany<MatchPeriodScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(MatchPeriodScore::class);
    }
}
