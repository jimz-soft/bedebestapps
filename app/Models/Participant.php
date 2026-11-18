<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'team_id',
    'name',
    'is_player',
    'is_personil',
    'is_captain',
    'is_co_captain',
])]
class Participant extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_player' => 'boolean',
            'is_personil' => 'boolean',
            'is_captain' => 'boolean',
            'is_co_captain' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function leadershipLabel(): ?string
    {
        if ($this->is_captain) {
            return 'Kapten';
        }

        if ($this->is_co_captain) {
            return 'Co-Kapten';
        }

        return null;
    }
}
