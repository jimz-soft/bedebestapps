<x-layouts.public :setting="$setting" active="leaderboard" :title="'Leaderboard | '.($setting->name ?? 'BedeBest')">
<main class="max-w-5xl mx-auto px-4 pt-28 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">Juara umum</p>
    <h1 class="font-gaming text-4xl mb-3 text-brand-gold">LEADERBOARD</h1>
    <p class="text-gray-400 text-sm mb-8">Total = poin games + poin personil. Keduanya diketik panitia per pertandingan (total akhir, bukan jumlah orang).</p>

    @if ($standings->isEmpty())
        <div class="bg-brand-card border border-white/5 rounded-2xl p-10 text-center">
            <p class="font-gaming text-lg mb-2">BELUM ADA GRUP</p>
            <p class="text-sm text-gray-500">Administrator belum mengisi master grup.</p>
        </div>
    @else
        <section class="roster-banner -mx-4 px-4 py-4 mb-10">
            <div class="roster-row">
                @foreach ($standings as $row)
                    <a href="{{ route('tim.show', $row['team']) }}" class="roster-card no-underline text-inherit {{ $row['rank'] === 1 ? 'roster-card-lead' : '' }}">
                        <p class="roster-mark">#{{ $row['rank'] }}</p>
                        <img class="roster-photo" src="{{ $row['team']->bannerUrl() }}" alt="Banner {{ $row['team']->name }}">
                        <p class="roster-plate">{{ strtoupper($row['team']->name) }}</p>
                        <p class="font-gaming text-center text-sm pb-3 -mt-1 {{ $row['rank'] === 1 ? 'text-brand-gold' : 'text-brand-cyan' }}">{{ $row['total'] }} PTS</p>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="overflow-x-auto">
            <div class="min-w-[640px] bg-brand-card border border-white/5 rounded-2xl overflow-hidden">
                <div class="grid grid-cols-12 text-[10px] uppercase tracking-wider text-gray-500 px-5 py-3 border-b border-white/10">
                    <span class="col-span-1">#</span>
                    <span class="col-span-4">Grup</span>
                    <span class="col-span-2 text-right">Games</span>
                    <span class="col-span-2 text-right">Personil</span>
                    <span class="col-span-3 text-right">Total</span>
                </div>
                @foreach ($standings as $row)
                    <a href="{{ route('tim.show', $row['team']) }}" class="grid grid-cols-12 items-center px-5 py-4 {{ $loop->last ? '' : 'border-b border-white/5' }} {{ $loop->even ? 'bg-white/[0.03]' : '' }} hover:bg-white/[0.05]">
                        <span class="col-span-1 font-gaming {{ $row['rank_color'] }}">{{ $row['rank'] }}</span>
                        <span class="col-span-4 flex items-center gap-2 min-w-0">
                            <x-team-avatar :team="$row['team']" size="w-7 h-7" />
                            <span class="truncate">{{ $row['team']->name }}</span>
                        </span>
                        <span class="col-span-2 text-right text-gray-400">{{ $row['poin_games'] }}</span>
                        <span class="col-span-2 text-right text-gray-400">{{ $row['poin_personil'] }}</span>
                        <span class="col-span-3 text-right font-gaming text-xl {{ $row['rank'] === 1 ? 'text-brand-gold' : '' }}">{{ $row['total'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-4">Poin masuk leaderboard setelah panitia menandai pertandingan selesai. Klik grup untuk melihat profil.</p>
    @endif
</main>
<style>
    .roster-card-lead { box-shadow: 0 0 36px rgba(228, 199, 107, .32); border-color: #e4c76b; }
</style>
</x-layouts.public>
