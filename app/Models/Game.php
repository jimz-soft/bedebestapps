<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'format',
    'requires_roster',
    'uses_period_score',
    'period_label',
    'period_count_default',
    'score_mode',
    'heat_size',
    'rules_html',
    'banner_path',
    'is_active',
])]
class Game extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_roster' => 'boolean',
            'uses_period_score' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Game>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Game>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @return HasMany<GameMatch, $this>
     */
    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }

    /**
     * @return HasMany<GameRoster, $this>
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(GameRoster::class);
    }

    public function formatLabel(): string
    {
        return match ($this->format) {
            'head_to_head' => 'Gugur',
            'round_robin' => 'Round robin',
            'heat' => 'Lomba bersama',
            default => $this->format,
        };
    }

    public function bannerUrl(): ?string
    {
        $uploaded = Media::url($this->banner_path);

        if ($uploaded) {
            return $uploaded;
        }

        foreach (['jpg', 'jpeg', 'png', 'webp', 'svg'] as $extension) {
            $relative = 'images/games/'.$this->slug.'.'.$extension;

            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    }

    public function formatSummary(): string
    {
        $parts = match ($this->format) {
            'head_to_head' => ['head-to-head', 'sistem gugur'],
            'round_robin' => ['round robin', 'semua grup ketemu semua'],
            'heat' => ['lomba bersama', max(2, (int) $this->heat_size).' grup dalam satu sesi'],
            default => [(string) $this->format],
        };

        if ($this->uses_period_score) {
            $label = strtolower((string) ($this->period_label ?: 'babak'));
            $parts[] = $this->score_mode === 'sets'
                ? 'skor per '.$label
                : 'skor per '.$label.' (akumulasi)';
        }

        return 'Format: '.implode(' · ', $parts);
    }

    public function sanitizedRules(): ?string
    {
        if ($this->rules_html === null || trim($this->rules_html) === '') {
            return null;
        }

        $html = strip_tags($this->rules_html, '<p><br><ul><ol><li><strong><em><b><i><a><u><span>');

        return $html === '' ? null : $html;
    }
}
