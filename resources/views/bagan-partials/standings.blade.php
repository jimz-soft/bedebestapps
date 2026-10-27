@php
    $game = $board['game'];
    $showSets = $showSets ?? false;
@endphp
<h3 class="text-xs uppercase tracking-widest text-gray-500 mb-3 {{ $game->format === 'head_to_head' ? 'mt-4' : '' }}">Klasemen {{ $game->name }}</h3>
<p class="text-xs text-gray-500 mb-3">Total = poin games + poin personil (keduanya diketik panitia, cabang ini saja).</p>
<div class="overflow-x-auto mb-2">
    <table class="w-full min-w-[640px] text-sm bg-brand-card border border-white/5 rounded-2xl overflow-hidden">
        <thead>
            <tr class="text-[10px] uppercase tracking-wider text-gray-500 border-b border-white/10">
                <th class="text-left px-4 py-3">#</th>
                <th class="text-left px-4 py-3">Grup</th>
                <th class="px-3 py-3">M</th>
                <th class="px-3 py-3">W</th>
                <th class="px-3 py-3">L</th>
                @if ($showSets)
                    <th class="px-3 py-3">Set</th>
                @endif
                <th class="text-right px-3 py-3">Poin games</th>
                <th class="text-right px-3 py-3">Poin personil</th>
                <th class="text-right px-4 py-3">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($board['standings'] as $row)
                <tr class="{{ $loop->last ? '' : 'border-b border-white/5' }} {{ $loop->even ? 'bg-white/[0.03]' : '' }}">
                    <td class="px-4 py-3 font-gaming {{ $row['rank_color'] }}">{{ $row['rank'] }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-2">
                            @if ($showSets)
                                <x-team-avatar :team="$row['team']" size="w-6 h-6" />
                            @endif
                            {{ $row['team']->name }}
                        </span>
                    </td>
                    <td class="text-center text-gray-400">{{ $row['played'] }}</td>
                    <td class="text-center">{{ $row['wins'] }}</td>
                    <td class="text-center">{{ $row['losses'] }}</td>
                    @if ($showSets)
                        <td class="text-center">{{ $row['sets_for'] }}–{{ $row['sets_against'] }}</td>
                    @endif
                    <td class="text-right">{{ $row['poin_games'] }}</td>
                    <td class="text-right text-gray-400">{{ $row['poin_personil'] }}</td>
                    <td class="text-right font-gaming {{ $row['rank'] === 1 ? 'text-brand-cyan' : '' }}">{{ $row['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="text-[11px] text-gray-600 mb-8">Poin masuk klasemen setelah panitia menandai pertandingan selesai.</p>
