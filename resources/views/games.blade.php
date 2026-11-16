<x-layouts.public :setting="$setting" active="games" :title="'Games | '.($setting->name ?? 'BedeBest')">
<style>
    /* Tailwind Preflight reset list-style; marker harus inside agar tidak terpotong overflow-hidden card. */
    .game-rules {
        color: #f3efe3;
        font-size: 1rem;
        line-height: 1.7;
        font-weight: 400;
        letter-spacing: .01em;
        -webkit-font-smoothing: antialiased;
    }
    .game-rules p { margin-bottom: .65rem; }
    .game-rules ul,
    .game-rules ol {
        margin: .65rem 0;
        padding-left: 0;
        list-style-position: inside;
    }
    .game-rules ul { list-style-type: disc; }
    .game-rules ol { list-style-type: decimal; }
    .game-rules li { margin-bottom: .45rem; color: #f3efe3; }
    .game-rules li > p { display: inline; margin: 0; }
    .game-rules strong,
    .game-rules b { color: #e4c76b; font-weight: 700; }
    .game-rules em,
    .game-rules i { color: #efe9d8; }
    .game-rules a { color: #22d3ee; text-decoration: underline; text-underline-offset: 2px; }
    .game-rules-wrap { position: relative; max-width: 42rem; }
    .game-rules-wrap[data-collapsed="true"] .game-rules {
        max-height: 5.5rem;
        overflow: hidden;
        -webkit-mask-image: linear-gradient(to bottom, #000 45%, transparent 100%);
        mask-image: linear-gradient(to bottom, #000 45%, transparent 100%);
    }
    .game-rules-wrap[data-collapsed="false"] .game-rules {
        max-height: none;
        -webkit-mask-image: none;
        mask-image: none;
    }
    .game-rules-wrap[data-short="true"] .game-rules-toggle { display: none; }
    .game-rules-toggle {
        margin-top: .65rem;
        padding: 0;
        border: 0;
        background: none;
        color: #06b6d4;
        font-size: .8rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        cursor: pointer;
    }
    .game-rules-toggle:hover { color: #67e8f9; }
</style>
<main class="max-w-5xl mx-auto px-4 pt-28 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">Cabang</p>
    <h1 class="font-gaming text-4xl mb-3 text-brand-gold">GAMES</h1>
    <p class="text-gray-400 text-sm mb-10">Games yang akan Di pertandingkan Selama Event </p>

    <div class="space-y-6">
        @forelse ($games as $game)
            <article id="{{ $game->slug }}" class="match-card bg-brand-card border border-white/5 rounded-2xl scroll-mt-28 overflow-hidden">
                <div class="relative aspect-video bg-gradient-to-br from-brand-purple/40 via-brand-dark to-brand-cyan/30">
                    @if ($game->bannerUrl())
                        <img src="{{ $game->bannerUrl() }}" alt="Banner {{ $game->name }}" class="absolute inset-0 w-full h-full object-cover">
                    @endif
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/80 to-transparent"></div>
                    <h2 class="absolute bottom-4 left-6 right-6 z-10 font-gaming text-2xl md:text-3xl drop-shadow-lg">{{ strtoupper($game->name) }}</h2>
                </div>
                <div class="p-6 flex flex-col md:flex-row md:items-start justify-between gap-4 overflow-visible">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-400">{{ $game->formatSummary() }}</p>
                        <p class="text-sm text-gray-400 mt-1">
                            Roster:
                            @if ($game->requires_roster)
                                <span class="text-brand-cyan">aktif</span>
                            @else
                                <span class="text-gray-500">tidak aktif</span>
                            @endif
                            @if (! $game->requires_roster && $game->format === 'heat')
                                · poin per grup diisi panitia setelah sesi
                            @endif
                        </p>
                        @if ($game->sanitizedRules())
                            <div class="game-rules-wrap mt-4" data-collapsed="true">
                                <div class="game-rules pl-1">
                                    {!! $game->sanitizedRules() !!}
                                </div>
                                <button type="button" class="game-rules-toggle" aria-expanded="false">More info</button>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('jadwal', ['game' => $game->slug]) }}" class="text-sm uppercase tracking-wider text-brand-cyan shrink-0">Lihat jadwal →</a>
                </div>
            </article>
        @empty
            <div class="bg-brand-card border border-white/5 rounded-2xl p-10 text-center">
                <p class="font-gaming text-lg mb-2">BELUM ADA CABANG</p>
                <p class="text-sm text-gray-500">Administrator belum mengaktifkan game.</p>
            </div>
        @endforelse
    </div>
</main>
<script>
(function () {
    document.querySelectorAll('.game-rules-wrap').forEach(function (wrap) {
        const rules = wrap.querySelector('.game-rules');
        const toggle = wrap.querySelector('.game-rules-toggle');
        if (!rules || !toggle) return;

        if (rules.scrollHeight <= rules.clientHeight + 4) {
            wrap.dataset.short = 'true';
            wrap.dataset.collapsed = 'false';
            return;
        }

        toggle.addEventListener('click', function () {
            const open = wrap.dataset.collapsed !== 'true';
            wrap.dataset.collapsed = open ? 'true' : 'false';
            toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
            toggle.textContent = open ? 'More info' : 'Show less';
        });
    });
})();
</script>
</x-layouts.public>
