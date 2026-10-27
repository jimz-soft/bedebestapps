<x-layouts.public :setting="$setting" active="games" :title="'Games | '.($setting->name ?? 'BedeBest')">
<style>
    .game-rules p { margin-bottom: .5rem; }
    .game-rules ul, .game-rules ol { margin: .5rem 0 .5rem 1.25rem; list-style: disc; }
    .game-rules a { color: #06b6d4; }
</style>
<main class="max-w-5xl mx-auto px-4 pt-28 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">Cabang</p>
    <h1 class="font-gaming text-4xl mb-3 text-brand-gold">GAMES</h1>
    <p class="text-gray-400 text-sm mb-10">Peraturan dan banner tiap cabang diisi administrator. Roster on/off diatur per game. Poin games + poin personil diketik panitia setelah bertanding.</p>

    <div class="space-y-6">
        @forelse ($games as $game)
            <article id="{{ $game->slug }}" class="match-card bg-brand-card border border-white/5 rounded-2xl overflow-hidden scroll-mt-28">
                <div class="relative aspect-video bg-gradient-to-br from-brand-purple/40 via-brand-dark to-brand-cyan/30">
                    @if ($game->bannerUrl())
                        <img src="{{ $game->bannerUrl() }}" alt="Banner {{ $game->name }}" class="absolute inset-0 w-full h-full object-cover">
                    @endif
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/80 to-transparent"></div>
                    <h2 class="absolute bottom-4 left-6 right-6 z-10 font-gaming text-2xl md:text-3xl drop-shadow-lg">{{ strtoupper($game->name) }}</h2>
                </div>
                <div class="p-6 flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div class="min-w-0">
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
                            <div class="game-rules text-sm text-gray-400 mt-4 max-w-2xl">
                                {!! $game->sanitizedRules() !!}
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
</x-layouts.public>
