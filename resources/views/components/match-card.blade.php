@props(['match'])

@php
    $sides = $match->matchTeams;
    $duel = $match->isDuel();
    $showScore = in_array($match->status, ['live', 'selesai'], true);
@endphp

<article {{ $attributes->merge(['class' => 'match-card bg-brand-card border rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6 '.($match->status === 'live' ? 'border-red-500/30' : 'border-white/5')]) }}>
    @if ($duel)
        @php
            $home = $sides[0];
            $away = $sides[1];
        @endphp
        <div class="flex items-center gap-4 w-full md:w-1/3">
            <x-team-avatar :team="$home->team" />
            <div>
                <h3 class="font-gaming font-bold flex items-center gap-1">{{ strtoupper($home->team->name) }} <x-winner-star :side="$home" /></h3>
                <span class="text-[10px]" style="color: {{ $home->team->color }}">{{ strtoupper($match->game->name) }} · {{ $match->round_label }}</span>
            </div>
        </div>
        <div class="text-center md:w-1/3 border-y md:border-y-0 md:border-x border-white/10 py-4 md:py-0">
            @if ($match->status === 'live')
                <p class="text-[10px] text-red-400 uppercase mb-1 flex items-center justify-center gap-2"><span class="w-2 h-2 rounded-full bg-red-500 live-dot animate-pulse"></span> Live</p>
                <p class="font-gaming text-4xl text-brand-cyan">{{ $home->score_cache ?? 0 }} <span class="text-brand-cyan">:</span> {{ $away->score_cache ?? 0 }}</p>
            @elseif ($match->status === 'selesai')
                <p class="text-[10px] text-gray-500 uppercase mb-1">Selesai</p>
                <p class="font-gaming text-4xl text-brand-cyan">{{ $home->score_cache ?? 0 }} <span class="text-brand-cyan">:</span> {{ $away->score_cache ?? 0 }}</p>
            @else
                <p class="font-gaming text-4xl text-brand-purple">{{ $match->scheduled_at?->format('H:i') ?? '--:--' }}</p>
                <p class="text-xs text-gray-400">{{ $match->scheduleStamp() }}</p>
            @endif
        </div>
        <div class="flex items-center justify-end gap-4 w-full md:w-1/3 text-right">
            <div>
                <h3 class="font-gaming font-bold flex items-center justify-end gap-1"><x-winner-star :side="$away" /> {{ strtoupper($away->team->name) }}</h3>
                <span class="text-[10px]" style="color: {{ $away->team->color }}">{{ strtoupper($match->game->name) }} · {{ $match->round_label }}</span>
            </div>
            <x-team-avatar :team="$away->team" />
        </div>
    @else
        <div class="flex -space-x-2">
            @foreach ($sides as $side)
                <x-team-avatar :team="$side->team" size="w-12 h-12" :tinted="false" class="border-[#0a0a0f]" />
            @endforeach
        </div>
        <div class="text-center">
            @if ($match->status === 'live')
                <p class="text-[10px] text-red-400 uppercase mb-1 flex items-center justify-center gap-2"><span class="w-2 h-2 rounded-full bg-red-500 live-dot animate-pulse"></span> Live</p>
            @elseif ($match->status === 'selesai')
                <p class="text-[10px] text-gray-500 uppercase mb-1">Selesai</p>
            @endif
            @if ($showScore)
                <p class="font-gaming text-2xl text-brand-cyan flex flex-wrap items-center justify-center gap-x-3 gap-y-1">
                    @foreach ($sides as $side)
                        <span class="inline-flex items-center gap-1">{{ $side->team->code }} {{ $side->score_cache ?? 0 }} <x-winner-star :side="$side" /></span>
                    @endforeach
                </p>
            @else
                <p class="font-gaming text-3xl text-brand-cyan">{{ $match->scheduled_at?->format('H:i') ?? '--:--' }}</p>
            @endif
            <p class="text-xs text-gray-400 uppercase mt-1">{{ $match->game->formatLabel() }} · {{ $sides->count() }} grup</p>
        </div>
        <div class="text-right">
            <p class="font-gaming font-bold">{{ strtoupper($match->game->name) }}</p>
            <span class="text-[10px] text-green-400">{{ strtoupper($match->location ?: $match->round_label) }}</span>
        </div>
    @endif
</article>
