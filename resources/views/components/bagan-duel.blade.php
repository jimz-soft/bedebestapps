@props(['match', 'featured' => false])

@php
    $sides = $match->matchTeams;
    $showScore = in_array($match->status, ['live', 'selesai'], true);
    $live = $match->status === 'live';
    $border = $live ? 'border-red-500/40' : ($featured ? 'border-brand-cyan/40' : 'border-white/10');
    $card = $featured ? 'bg-gradient-to-b from-brand-purple/20 to-brand-card' : 'bg-brand-card';
@endphp

<div {{ $attributes->merge(['class' => $card.' border '.$border.' rounded-xl overflow-hidden']) }}>
    @if ($live)
        <p class="text-[10px] text-center text-red-400 py-1 border-b border-white/5 flex justify-center items-center gap-1"><span class="w-1.5 h-1.5 bg-red-500 rounded-full live-dot"></span> Live</p>
    @endif
    @foreach ($sides as $side)
        <div class="slot flex justify-between px-3 {{ $featured ? 'py-3' : 'py-2' }} {{ $loop->first ? 'border-b border-white/5' : '' }} text-sm">
            <span class="inline-flex items-center gap-1">{{ $side->team->name }} <x-winner-star :side="$side" /></span>
            @if ($showScore)
                <span class="font-gaming text-brand-cyan">{{ $side->score_cache ?? 0 }}</span>
            @else
                <span class="font-gaming text-gray-500">—</span>
            @endif
        </div>
    @endforeach
    @if ($match->timeLocation() !== '')
        <p class="text-[10px] text-center {{ $featured ? 'text-brand-cyan uppercase' : 'text-gray-500' }} py-2 border-t {{ $featured ? 'border-white/10' : 'border-white/5' }}">{{ $match->timeLocation() }}</p>
    @endif
</div>
