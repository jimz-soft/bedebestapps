<x-layouts.public :setting="$setting" active="tim" :title="'Tim | '.($setting->name ?? 'BedeBest')">
<main class="max-w-6xl mx-auto px-4 pt-28 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">Groups</p>
    <h1 class="font-gaming text-4xl mb-10 text-brand-gold">{{ $heading }}</h1>
    <div class="grid md:grid-cols-2 gap-6">
        @forelse ($groups as $group)
            @php
                $team = $group['team'];
                $rankColor = match ($group['rank']) {
                    1 => 'text-brand-gold',
                    2 => 'text-[#c0c7d1]',
                    3 => 'text-[#cd7f32]',
                    default => 'text-white',
                };
            @endphp
            <a href="{{ route('tim.show', $team) }}" class="bg-brand-card border border-white/5 rounded-2xl p-6 flex gap-5 hover:border-[color:var(--team-color)]/50" style="--team-color: {{ $team->color }}">
                <x-team-avatar :team="$team" size="w-20 h-20" />
                <div>
                    <h2 class="font-gaming text-2xl">{{ $team->name }}</h2>
                    <p class="text-sm text-gray-400 mt-1">Peringkat <span class="font-gaming {{ $rankColor }}">#{{ $group['rank'] }}</span> · {{ $group['total'] }} poin</p>
                    <p class="text-xs text-gray-500 mt-2">{{ $group['peserta'] }} peserta · {{ $group['kapten'] }} kapten · Games {{ $group['poin_games'] }} · Personil {{ $group['poin_personil'] }}</p>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 bg-brand-card border border-white/5 rounded-2xl p-10 text-center">
                <p class="font-gaming text-lg mb-2">BELUM ADA GRUP</p>
                <p class="text-sm text-gray-500">Administrator belum mengisi master grup.</p>
            </div>
        @endforelse
    </div>
</main>
</x-layouts.public>
