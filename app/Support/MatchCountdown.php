<?php

namespace App\Support;

use App\Models\GameMatch;

class MatchCountdown
{
    /**
     * @return array{days: string, hours: string, minutes: string, seconds: string, remaining: int, live: bool, label: string}
     */
    public function current(): array
    {
        $zero = [
            'days' => '00',
            'hours' => '00',
            'minutes' => '00',
            'seconds' => '00',
            'remaining' => 0,
            'live' => false,
            'label' => '',
        ];

        $live = GameMatch::query()
            ->with('game')
            ->where('status', 'live')
            ->orderByRaw('scheduled_at is null')
            ->orderBy('scheduled_at')
            ->first();

        if ($live !== null) {
            return array_merge($zero, [
                'live' => true,
                'label' => $this->label('Sedang berlangsung', $live),
            ]);
        }

        $next = GameMatch::query()
            ->with('game')
            ->where('status', 'upcoming')
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->first();

        if ($next === null || $next->scheduled_at === null) {
            return $zero;
        }

        if ($next->scheduled_at->lessThanOrEqualTo(now())) {
            return array_merge($zero, [
                'label' => $this->label('Menuju', $next),
            ]);
        }

        $diff = now()->diff($next->scheduled_at);

        return [
            'days' => str_pad((string) $diff->days, 2, '0', STR_PAD_LEFT),
            'hours' => str_pad((string) $diff->h, 2, '0', STR_PAD_LEFT),
            'minutes' => str_pad((string) $diff->i, 2, '0', STR_PAD_LEFT),
            'seconds' => str_pad((string) $diff->s, 2, '0', STR_PAD_LEFT),
            'remaining' => max(0, $next->scheduled_at->getTimestamp() - now()->getTimestamp()),
            'live' => false,
            'label' => $this->label('Menuju', $next),
        ];
    }

    private function label(string $prefix, GameMatch $match): string
    {
        $detail = implode(' · ', array_filter([
            $match->game?->name,
            $match->round_label,
        ]));

        return $detail === '' ? $prefix : $prefix.' · '.$detail;
    }
}
