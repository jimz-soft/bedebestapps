<x-layouts.public :setting="$setting" active="bagan" :title="'Bagan | '.($setting->name ?? 'BedeBest')">
<style>
    .bagan-panel { display: none; }
    .bagan-panel.active { display: block; }
    .tab-btn.active { background: linear-gradient(to right, #8b5cf6, #06b6d4); color: #fff; border-color: transparent; }
    .slot { min-height: 42px; }
</style>
<main class="max-w-6xl mx-auto px-4 pt-28 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">Per cabang</p>
    <h1 class="font-gaming text-4xl mb-3 text-brand-gold">BAGAN</h1>
    @php
        $intro = collect($boards)->map(fn ($item) => $item['game']->name.': '.$item['caption'])->join('. ');
    @endphp
    <p class="text-gray-400 text-sm mb-8">Setiap game punya bagan sendiri.{{ $intro !== '' ? ' '.$intro.'.' : '' }}</p>

    @if ($boards === [])
        <div class="bg-brand-card border border-white/5 rounded-2xl p-10 text-center">
            <p class="font-gaming text-lg mb-2">BELUM ADA BAGAN</p>
            <p class="text-sm text-gray-500">Administrator belum mengaktifkan game.</p>
        </div>
    @else
        <div class="flex flex-wrap gap-2 mb-10" id="tabs">
            @foreach ($boards as $board)
                <a href="{{ route('bagan', ['game' => $board['game']->slug]) }}#{{ $board['game']->slug }}" data-tab="{{ $board['game']->slug }}" class="tab-btn px-5 py-2 rounded-full border border-white/10 text-sm font-semibold uppercase tracking-wider {{ $selectedSlug === $board['game']->slug ? 'active' : 'text-gray-300' }}">{{ $board['game']->name }}</a>
            @endforeach
        </div>

        @foreach ($boards as $board)
            @php
                $game = $board['game'];
                $isActive = $selectedSlug === $game->slug;
            @endphp
            <section id="panel-{{ $game->slug }}" class="bagan-panel {{ $isActive ? 'active' : '' }}">
                <div class="flex items-end justify-between mb-6 gap-4">
                    <div>
                        <h2 class="font-gaming text-xl uppercase">{{ $game->name }} — {{ $board['caption'] }}</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            @if ($game->format === 'head_to_head')
                                Pohon gugur mengikuti jadwal panitia · klik tab lain untuk cabang berbeda
                            @elseif ($game->format === 'round_robin')
                                Semua grup ketemu semua. Bukan pohon gugur. Poin ranking diisi panitia setelah match.
                            @else
                                Bukan VS dua sisi. Satu sesi beberapa grup, poin games diisi panitia per grup.
                            @endif
                        </p>
                    </div>
                    @if ($board['liveCount'] > 0)
                        <span class="text-[10px] uppercase text-red-400 flex items-center gap-2 shrink-0"><span class="w-2 h-2 rounded-full bg-red-500 live-dot"></span> {{ $board['liveCount'] }} live</span>
                    @endif
                </div>

                @if ($game->format === 'head_to_head')
                    @if ($board['rounds']->isEmpty())
                        <div class="bg-brand-card border border-white/5 rounded-2xl p-8 text-center mb-8">
                            <p class="text-sm text-gray-500">Bagan gugur tampil setelah jadwal diisi panitia.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto pb-4">
                            <div class="flex items-start justify-center gap-8 min-w-[720px] py-6">
                                @foreach ($board['rounds'] as $round)
                                    <div class="{{ $round['featured'] ? 'w-56' : 'space-y-10 w-56' }}">
                                        <p class="text-[10px] uppercase text-center {{ $round['featured'] ? 'text-brand-cyan mb-10' : 'text-gray-500' }}">{{ $round['label'] }}</p>
                                        <div class="space-y-10">
                                            @foreach ($round['matches'] as $match)
                                                <x-bagan-duel :match="$match" :featured="$round['featured']" />
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                @if ($game->format === 'round_robin')
                    @include('bagan-partials.standings', ['board' => $board, 'showSets' => true])

                    <h3 class="text-xs uppercase tracking-widest text-gray-500 mb-3">Matriks hasil</h3>
                    @if ($board['matrix'])
                        <div class="overflow-x-auto mb-10">
                            <table class="w-full min-w-[520px] text-center text-sm bg-brand-card border border-white/5 rounded-2xl overflow-hidden">
                                <thead>
                                    <tr class="text-[10px] uppercase text-gray-500 border-b border-white/10">
                                        <th class="p-3 text-left">vs</th>
                                        @foreach ($board['matrix']['teams'] as $column)
                                            <th class="p-3">{{ $column->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($board['matrix']['teams'] as $row)
                                        <tr class="{{ $loop->last ? '' : 'border-b border-white/5' }} {{ $loop->even ? 'bg-white/[0.03]' : '' }}">
                                            <td class="p-3 text-left font-gaming text-xs">{{ $row->name }}</td>
                                            @foreach ($board['matrix']['teams'] as $column)
                                                @php $cell = $board['matrix']['cells'][$row->id][$column->id]; @endphp
                                                <td class="p-3 {{ $cell['type'] === 'self' ? 'text-gray-700' : ($cell['type'] === 'date' || $cell['type'] === 'empty' ? 'text-gray-500' : ($cell['won'] === true ? 'text-brand-cyan' : ($cell['won'] === false ? 'text-red-400' : 'text-gray-300'))) }}">
                                                    {{ $cell['text'] }}
                                                    @if ($cell['live'])
                                                        <span class="text-[10px] uppercase text-red-400">live</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <h3 class="text-xs uppercase tracking-widest text-gray-500 mb-3">Daftar pertandingan (semua vs semua)</h3>
                    <div class="space-y-3">
                        @forelse ($board['matches'] as $match)
                            @php
                                $home = $match->matchTeams[0] ?? null;
                                $away = $match->matchTeams[1] ?? null;
                                $showScore = in_array($match->status, ['live', 'selesai'], true);
                                $periodLine = $match->periodLine();
                                $periodLabel = strtolower((string) ($game->period_label ?: 'set'));
                            @endphp
                            <div class="bg-brand-card border {{ $match->status === 'live' ? 'border-red-500/30' : 'border-white/5' }} rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm {{ $showScore ? '' : 'text-gray-400' }}">
                                <span class="flex items-center gap-2 flex-wrap">
                                    @if ($match->status === 'live')
                                        <span class="w-2 h-2 bg-red-500 rounded-full live-dot"></span>
                                    @endif
                                    <span class="inline-flex items-center gap-1">{{ $home?->team->name }} @if ($home)<x-winner-star :side="$home" />@endif</span>
                                    @if ($showScore)
                                        <span class="text-brand-cyan font-gaming mx-1">{{ $home->score_cache ?? 0 }}–{{ $away->score_cache ?? 0 }}</span>
                                    @else
                                        <span class="mx-1">vs</span>
                                    @endif
                                    <span class="inline-flex items-center gap-1">@if ($away)<x-winner-star :side="$away" />@endif {{ $away?->team->name }}</span>
                                </span>
                                <span class="text-xs {{ $match->status === 'live' ? 'text-red-400 uppercase' : 'text-gray-500' }}">
                                    @if ($match->status === 'live')
                                        Live{{ $periodLine ? ' · '.$periodLabel.' '.$periodLine : '' }}
                                    @elseif ($match->status === 'selesai')
                                        Selesai{{ $periodLine ? ' · '.ucfirst($periodLabel).' '.$periodLine : '' }}
                                    @else
                                        {{ $match->timeLocation() }}
                                    @endif
                                </span>
                            </div>
                        @empty
                            <div class="bg-brand-card border border-white/5 rounded-xl p-6 text-sm text-gray-500">Belum ada pertandingan round robin.</div>
                        @endforelse
                    </div>
                @endif

                @if ($game->format === 'heat')
                    @forelse ($board['matches'] as $match)
                        @php
                            $rankedSides = $match->matchTeams
                                ->sortBy([
                                    fn ($side) => -((int) $side->points_awarded + (int) $side->bonus_pemenang + (int) $side->poin_personil),
                                    fn ($side) => $side->sort_order,
                                ])
                                ->values();
                        @endphp
                        <div class="bg-brand-card border {{ $match->status === 'live' ? 'border-red-500/30' : 'border-white/5' }} rounded-2xl p-6 max-w-xl mb-6">
                            <div class="flex justify-between items-center mb-5 gap-3">
                                <div>
                                    <p class="font-gaming">{{ $match->round_label ?: $game->name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $match->location ? $match->location.' · ' : '' }}{{ $match->scheduled_at?->format('H:i') }}
                                        · roster {{ $game->requires_roster ? 'on' : 'off' }}
                                    </p>
                                </div>
                                <a href="{{ route('jadwal', ['game' => $game->slug]) }}" class="text-xs uppercase tracking-wider text-brand-cyan shrink-0">Lihat jadwal →</a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[520px] text-sm">
                                    <thead>
                                        <tr class="text-[10px] uppercase text-gray-500 border-b border-white/10">
                                            <th class="text-left py-2">#</th>
                                            <th class="text-left py-2">Grup</th>
                                            <th class="text-right py-2">Poin games</th>
                                            <th class="text-right py-2">Poin personil</th>
                                            <th class="text-right py-2">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rankedSides as $side)
                                            @php
                                                $rank = $loop->iteration;
                                                $rankColor = match ($rank) {
                                                    1 => 'text-brand-gold',
                                                    2 => 'text-[#c0c7d1]',
                                                    3 => 'text-[#cd7f32]',
                                                    default => 'text-white',
                                                };
                                                $poinGames = (int) $side->points_awarded + (int) $side->bonus_pemenang;
                                                $total = $poinGames + (int) $side->poin_personil;
                                            @endphp
                                            <tr class="{{ $loop->last ? '' : 'border-b border-white/5' }} {{ $rank === 1 && $total > 0 ? 'bg-yellow-500/10' : '' }}">
                                                <td class="py-3 font-gaming {{ $rankColor }}">{{ $rank }}</td>
                                                <td><span class="inline-flex items-center gap-1">{{ $side->team->name }} <x-winner-star :side="$side" /></span></td>
                                                <td class="text-right">{{ $poinGames }}</td>
                                                <td class="text-right text-gray-400">{{ $side->poin_personil }}</td>
                                                <td class="text-right font-gaming {{ $rank === 1 ? 'text-brand-cyan' : '' }}">{{ $total }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-3">Total = poin games + poin personil (diketik panitia).</p>
                        </div>
                    @empty
                        <div class="bg-brand-card border border-white/5 rounded-2xl p-8 text-center">
                            <p class="text-sm text-gray-500">Belum ada sesi lomba bersama.</p>
                        </div>
                    @endforelse
                @endif

                @if ($game->format !== 'heat')
                    @if ($game->format === 'head_to_head')
                        @include('bagan-partials.standings', ['board' => $board, 'showSets' => false])
                    @endif
                @endif
            </section>
        @endforeach
    @endif
</main>
<script>
(function () {
    const tabs = document.querySelectorAll('.tab-btn');
    const panels = {};
    document.querySelectorAll('.bagan-panel').forEach((el) => {
        panels[el.id.replace('panel-', '')] = el;
    });
    function show(id) {
        if (!panels[id]) return;
        tabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.tab === id));
        Object.entries(panels).forEach(([key, el]) => el.classList.toggle('active', key === id));
        history.replaceState(null, '', '#' + id);
    }
    tabs.forEach((tab) => tab.addEventListener('click', (event) => {
        event.preventDefault();
        show(tab.dataset.tab);
    }));
    const hash = (location.hash || '').replace('#', '');
    if (panels[hash]) show(hash);
})();
</script>
</x-layouts.public>
