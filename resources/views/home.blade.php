<x-layouts.public :setting="$setting" active="home" :title="($setting->name ?? 'BedeBest').' Tournament'">
<section class="relative pt-32 pb-16 min-h-[80vh] flex items-center overflow-hidden">
    <div class="absolute inset-0 bg-brand-dark/90"></div>
    <div class="absolute inset-0 bg-cover bg-center opacity-15" style="background-image:url('{{ $setting?->heroBannerUrl() ?? 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=2070&auto=format&fit=crop' }}')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-purple/20 rounded-full blur-[120px]"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10 text-center">
        @if ($setting?->logoUrl())
            <img src="{{ $setting->logoUrl() }}" alt="{{ $setting->name }}" class="w-20 h-20 rounded-full object-cover mx-auto mb-6 shadow-[0_0_30px_rgba(139,92,246,.5)]">
        @else
            <span class="w-20 h-20 rounded-full bg-gradient-to-br from-brand-purple to-brand-cyan inline-flex items-center justify-center font-gaming font-black text-2xl mb-6 shadow-[0_0_30px_rgba(139,92,246,.5)]">BB</span>
        @endif
        <p class="text-brand-cyan uppercase tracking-[0.3em] text-sm font-semibold mb-4">{{ $setting->hero_kicker ?? 'Company Gathering' }}</p>
        <h1 class="font-gaming text-4xl md:text-6xl font-bold mb-6">{{ strtoupper($setting->name ?? 'BEDEBEST') }}<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-cyan to-brand-purple neon-text">{{ $setting->hero_highlight ?? 'TOURNAMENT' }}</span></h1>
        <p class="text-gray-400 max-w-xl mx-auto mb-8 tracking-wide">
            @if ($countdown['live'])
                Pertandingan sedang berlangsung.
            @elseif ($countdown['label'] !== '')
                Countdown menuju pertandingan berikutnya.
            @else
                Jadwal pertandingan menyusul.
            @endif
        </p>
        <div id="match-countdown" class="flex justify-center flex-wrap gap-3 sm:gap-4 mb-3" data-remaining="{{ $countdown['remaining'] }}" data-live="{{ $countdown['live'] ? '1' : '0' }}">
            <div class="bg-brand-card/80 border border-white/10 rounded-2xl px-4 sm:px-5 py-4 min-w-[68px]"><p id="countdown-days" class="font-gaming text-3xl text-brand-cyan">{{ $countdown['days'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Hari</p></div>
            <div class="bg-brand-card/80 border border-white/10 rounded-2xl px-4 sm:px-5 py-4 min-w-[68px]"><p id="countdown-hours" class="font-gaming text-3xl text-brand-cyan">{{ $countdown['hours'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Jam</p></div>
            <div class="bg-brand-card/80 border border-white/10 rounded-2xl px-4 sm:px-5 py-4 min-w-[68px]"><p id="countdown-minutes" class="font-gaming text-3xl text-brand-cyan">{{ $countdown['minutes'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Menit</p></div>
            <div class="bg-brand-card/80 border border-white/10 rounded-2xl px-4 sm:px-5 py-4 min-w-[68px]"><p id="countdown-seconds" class="font-gaming text-3xl text-brand-cyan">{{ $countdown['seconds'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Detik</p></div>
        </div>
        @if ($countdown['label'] !== '')
            <p id="countdown-label" class="text-xs uppercase tracking-[0.2em] text-gray-500 mb-10">{{ $countdown['label'] }}</p>
        @else
            <p id="countdown-label" class="text-xs uppercase tracking-[0.2em] text-gray-500 mb-10">Belum ada jadwal pertandingan</p>
        @endif
        <div class="flex justify-center gap-4">
            <a href="{{ route('jadwal') }}" class="bg-gradient-to-r from-brand-purple to-brand-magenta px-8 py-3.5 rounded-full font-bold text-sm uppercase">Lihat Jadwal</a>
            <a href="{{ route('leaderboard') }}" class="border border-brand-cyan text-brand-cyan px-8 py-3.5 rounded-full font-bold text-sm uppercase">Leaderboard</a>
        </div>
        <div class="mt-16 relative h-[180px] max-w-4xl mx-auto">
            <div class="bg-shape clip-polygon bg-gradient-to-br from-brand-purple to-transparent left-10"></div>
            <div class="bg-shape clip-polygon bg-gradient-to-b from-brand-cyan to-transparent left-[30%]"></div>
            <div class="bg-shape clip-polygon-reverse bg-gradient-to-bl from-brand-magenta to-transparent right-10"></div>
        </div>
    </div>
</section>

<section class="px-4 relative z-10 roster-banner pt-2 pb-4">
    <div class="max-w-5xl mx-auto roster-row">
        @foreach ($teams as $team)
            <article class="roster-card">
                <p class="roster-mark">{{ strtoupper(substr($team->code, 0, 1)) }}</p>
                <img class="roster-photo" src="{{ $team->bannerUrl() }}" alt="{{ $team->name }}">
                <p class="roster-plate">{{ strtoupper($team->name) }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="px-4 relative z-10" id="live-score" data-endpoint="{{ route('live-score') }}">
    <div id="live-score-card" class="max-w-5xl mx-auto match-card flex flex-col md:flex-row items-center justify-between gap-6 bg-brand-card border border-red-500/30 rounded-2xl p-6 {{ $liveMatch ? '' : 'hidden' }}">
        <span id="live-score-label" class="px-3 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/40 text-xs uppercase flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-red-500 live-dot animate-pulse"></span> <span id="live-score-meta">@if ($liveMatch) Live · {{ $liveMatch->game->name }} · {{ $liveMatch->round_label }} @endif</span></span>
        <div id="live-score-sides" class="flex items-center gap-6 w-full md:w-auto justify-between flex-wrap">
            @if ($liveMatch)
                @php $sides = $liveMatch->matchTeams; @endphp
                @foreach ($sides as $side)
                    <div class="flex items-center gap-3 {{ $sides->count() === 2 && $loop->last ? 'flex-row-reverse' : '' }}">
                        <x-team-avatar :team="$side->team" size="w-12 h-12" />
                        <span class="font-gaming font-bold">{{ strtoupper($side->team->name) }}</span>
                        @if ($sides->count() !== 2)
                            <span class="font-gaming text-2xl">{{ $side->score_cache ?? 0 }}</span>
                        @endif
                    </div>
                    @if ($sides->count() === 2 && $loop->first)
                        <div class="font-gaming text-3xl font-black"><span data-live-left>{{ $sides[0]->score_cache ?? 0 }}</span> <span class="text-brand-cyan">:</span> <span data-live-right>{{ $sides[1]->score_cache ?? 0 }}</span></div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
    <div id="live-score-empty" class="max-w-5xl mx-auto bg-brand-card border border-white/5 rounded-2xl p-6 text-center text-sm text-gray-500 {{ $liveMatch ? 'hidden' : '' }}">Belum ada pertandingan live.</div>
</section>

<section class="py-16 max-w-5xl mx-auto px-4">
    <div class="text-center mb-10">
        <p class="text-brand-purple uppercase tracking-[0.2em] text-sm font-bold mb-2">Upcoming</p>
        <h2 class="font-gaming text-3xl uppercase">Jadwal <span class="text-brand-cyan">Pertandingan</span></h2>
    </div>
    <div class="space-y-6">
        @forelse ($upcomingMatches as $match)
            <x-match-card :match="$match" />
        @empty
            <p class="text-center text-sm text-gray-500">Jadwal pertandingan belum diisi panitia.</p>
        @endforelse
    </div>
</section>

<section class="pb-16 max-w-6xl mx-auto px-4 grid lg:grid-cols-2 gap-10">
    <div>
        <h2 class="font-gaming text-2xl uppercase mb-6">Games</h2>
        <div class="space-y-3">
            @foreach ($games as $game)
                <a href="{{ route('games') }}#{{ $game->slug }}" class="bg-brand-card border border-white/5 rounded-xl p-4 flex justify-between block">
                    <span>
                        <span class="font-gaming font-bold">{{ $game->name }}</span>
                        <span class="block text-xs text-gray-400 mt-1">{{ $game->formatLabel() }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    <div>
        <h2 class="font-gaming text-2xl uppercase mb-6">Top 4</h2>
        <div id="live-standings" class="bg-brand-card border border-white/5 rounded-2xl divide-y divide-white/5">
            @foreach ($standings->take(4) as $row)
                @php
                    $rankColor = match ($loop->iteration) {
                        1 => 'text-brand-gold',
                        2 => 'text-[#c0c7d1]',
                        3 => 'text-[#cd7f32]',
                        default => 'text-white',
                    };
                @endphp
                <a href="{{ route('leaderboard') }}" class="grid grid-cols-12 items-center px-5 py-4 {{ $loop->even ? 'bg-white/[0.03]' : '' }}">
                    <span class="col-span-1 font-gaming {{ $rankColor }}">{{ $loop->iteration }}</span>
                    <span class="col-span-7">{{ $row['team']->name }}</span>
                    <span class="col-span-4 text-right font-gaming">{{ $row['total'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="pb-20 max-w-6xl mx-auto px-4">
    <h2 class="font-gaming text-2xl uppercase mb-6">Grup</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($teams as $team)
            <a href="{{ route('tim.show', $team) }}" class="bg-brand-card border border-white/5 rounded-2xl p-5 text-center hover:border-brand-purple/40">
                <x-team-avatar :team="$team" class="mx-auto mb-3" />
                <p class="font-gaming text-sm">{{ $team->name }}</p>
            </a>
        @endforeach
    </div>
</section>
<script>
(function () {
    const box = document.getElementById('match-countdown');
    const remaining = Number(box && box.dataset.remaining);
    if (!box || box.dataset.live === '1' || !Number.isFinite(remaining) || remaining <= 0) {
        return;
    }

    const daysEl = document.getElementById('countdown-days');
    const hoursEl = document.getElementById('countdown-hours');
    const minutesEl = document.getElementById('countdown-minutes');
    const secondsEl = document.getElementById('countdown-seconds');
    const endsAt = Date.now() + (remaining * 1000);

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function tickCountdown() {
        const remain = Math.max(0, endsAt - Date.now());
        const totalSeconds = Math.floor(remain / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        daysEl.textContent = pad(days);
        hoursEl.textContent = pad(hours);
        minutesEl.textContent = pad(minutes);
        secondsEl.textContent = pad(seconds);
    }

    tickCountdown();
    setInterval(tickCountdown, 250);
})();

(function () {
    const root = document.getElementById('live-score');
    if (!root) return;
    const endpoint = root.dataset.endpoint;
    const card = document.getElementById('live-score-card');
    const empty = document.getElementById('live-score-empty');
    const meta = document.getElementById('live-score-meta');
    const sidesBox = document.getElementById('live-score-sides');
    const standingsBox = document.getElementById('live-standings');
    const rankColor = ['text-brand-gold', 'text-[#c0c7d1]', 'text-[#cd7f32]', 'text-white'];

    function avatarHtml(side) {
        const border = 'style="border-color:' + side.color + '"';
        if (side.logo) {
            return '<img src="' + side.logo + '" alt="' + side.name + '" class="w-12 h-12 rounded-full border-2 object-cover shrink-0" ' + border + '>';
        }
        return '<span class="w-12 h-12 rounded-full border-2 inline-flex items-center justify-center font-gaming text-[11px] shrink-0" ' + border + '>' + side.code + '</span>';
    }

    function sideHtml(side, reverse) {
        return '<div class="flex items-center gap-3 ' + (reverse ? 'flex-row-reverse' : '') + '">' +
            avatarHtml(side) +
            '<span class="font-gaming font-bold">' + side.name.toUpperCase() + '</span>' +
            '</div>';
    }

    function renderLive(live) {
        if (!live) {
            card.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');
        card.classList.remove('hidden');
        meta.textContent = 'Live · ' + live.game + ' · ' + (live.round || '');
        const sides = live.sides || [];
        if (sides.length === 2) {
            sidesBox.innerHTML = sideHtml(sides[0], false) +
                '<div class="font-gaming text-3xl font-black">' + sides[0].score + ' <span class="text-brand-cyan">:</span> ' + sides[1].score + '</div>' +
                sideHtml(sides[1], true);
            return;
        }
        sidesBox.innerHTML = sides.map(function (side) {
            return sideHtml(side, false) + '<span class="font-gaming text-2xl">' + side.score + '</span>';
        }).join('');
    }

    function renderStandings(rows) {
        if (!standingsBox || !rows) return;
        standingsBox.innerHTML = rows.map(function (row, index) {
            const even = index % 2 === 1 ? ' bg-white/[0.03]' : '';
            return '<a href="{{ route('leaderboard') }}" class="grid grid-cols-12 items-center px-5 py-4' + even + '">' +
                '<span class="col-span-1 font-gaming ' + (rankColor[index] || 'text-white') + '">' + (index + 1) + '</span>' +
                '<span class="col-span-7">' + row.name + '</span>' +
                '<span class="col-span-4 text-right font-gaming">' + row.total + '</span>' +
                '</a>';
        }).join('');
    }

    async function tick() {
        try {
            const response = await fetch(endpoint, { cache: 'no-store', headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            renderLive(data.live);
            renderStandings(data.standings);
        } catch (e) {}
    }

    setInterval(tick, 4000);
})();
</script>
</x-layouts.public>
