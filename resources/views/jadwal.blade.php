<x-layouts.public :setting="$setting" active="jadwal" :title="'Jadwal | '.($setting->name ?? 'BedeBest')">
<main class="max-w-5xl mx-auto px-4 pt-32 pb-20">
    <p class="text-brand-purple uppercase tracking-[0.2em] text-sm font-bold mb-2">Tournament</p>
    <h1 class="font-gaming text-4xl md:text-5xl uppercase mb-3">Jadwal <span class="text-brand-cyan">Pertandingan</span></h1>
    <p class="text-gray-400 text-sm mb-8">Semua cabang. Live menampilkan skor, pertandingan berikutnya menampilkan jam.</p>

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('jadwal', array_filter(['status' => $selectedStatus])) }}" class="px-4 py-1.5 rounded-full text-[11px] uppercase tracking-wider {{ $selectedGame === null ? 'bg-brand-purple text-white' : 'border border-white/10 text-gray-400 hover:text-brand-cyan' }}">Semua</a>
        @foreach ($games as $game)
            <a href="{{ route('jadwal', array_filter(['game' => $game->slug, 'status' => $selectedStatus])) }}" class="px-4 py-1.5 rounded-full text-[11px] uppercase tracking-wider {{ $selectedGame?->id === $game->id ? 'bg-brand-purple text-white' : 'border border-white/10 text-gray-400 hover:text-brand-cyan' }}">{{ $game->name }}</a>
        @endforeach
    </div>
    <div class="flex flex-wrap gap-2 mb-10">
        @foreach ([null => 'Semua', 'live' => 'Live', 'upcoming' => 'Mendatang', 'selesai' => 'Selesai'] as $value => $label)
            @php $statusKey = $value === '' ? null : $value; @endphp
            <a href="{{ route('jadwal', array_filter(['game' => $selectedGame?->slug, 'status' => $statusKey])) }}" class="px-4 py-1.5 rounded-full text-[11px] uppercase tracking-wider {{ $selectedStatus === $statusKey ? ($statusKey === 'live' ? 'bg-red-500/20 text-red-400 border border-red-500/40' : 'bg-brand-purple text-white') : 'border border-white/10 text-gray-400 hover:text-brand-cyan' }}">{{ $label }}</a>
        @endforeach
    </div>

    @forelse ($days as $day)
        <section class="mb-12">
            <h2 class="font-gaming text-sm uppercase tracking-[0.2em] text-brand-gold mb-5">{{ $day['label'] }}</h2>
            <div class="space-y-6">
                @foreach ($day['matches'] as $match)
                    <x-match-card :match="$match" />
                @endforeach
            </div>
        </section>
    @empty
        <div class="bg-brand-card border border-white/5 rounded-2xl p-10 text-center">
            <p class="font-gaming text-lg mb-2">BELUM ADA JADWAL</p>
            <p class="text-sm text-gray-500">Jadwal pertandingan belum diisi panitia.</p>
        </div>
    @endforelse
</main>
</x-layouts.public>
