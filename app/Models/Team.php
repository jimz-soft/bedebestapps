<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'code',
    'color',
    'logo_path',
    'banner_path',
    'motto',
    'sort_order',
])]
class Team extends Model
{
    /**
     * @return HasMany<Participant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<MatchTeam, $this>
     */
    public function matchTeams(): HasMany
    {
        return $this->hasMany(MatchTeam::class);
    }

    /**
     * @return HasMany<GameRoster, $this>
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(GameRoster::class);
    }

    public function bannerUrl(): string
    {
        return Media::url($this->banner_path) ?? asset('images/teams/'.$this->slug.'.jpg');
    }

    public function logoUrl(): ?string
    {
        return Media::url($this->logo_path);
    }
}
