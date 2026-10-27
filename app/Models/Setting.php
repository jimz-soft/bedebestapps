<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'starts_at',
    'ends_at',
    'venue',
    'logo_path',
    'logo_icon_path',
    'hero_banner_path',
    'hero_kicker',
    'hero_highlight',
    'hero_body',
])]
class Setting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function heroBannerUrl(): ?string
    {
        return Media::url($this->hero_banner_path);
    }

    public function logoUrl(): ?string
    {
        return Media::url($this->logo_path);
    }
}
