@extends('layouts.tim')

@section('title', 'Profil tim')

@section('content')
<section class="space-y-8">
    <div class="bg-brand-card border border-white/5 rounded-2xl p-6 flex flex-col md:flex-row gap-6 items-start">
        <img src="{{ $team->bannerUrl() }}" alt="{{ $team->name }}" class="w-40 h-52 object-cover object-top rounded-xl border border-brand-gold/40">
        <div>
            <p class="text-[10px] uppercase tracking-widest text-brand-purple mb-2">Profil tim</p>
            <h1 class="font-gaming text-3xl mb-2">{{ strtoupper($team->name) }}</h1>
            <p class="font-gaming text-sm mb-3" style="color: {{ $team->color }}">{{ $team->code }}</p>
            @if ($team->motto)
                <p class="text-sm text-gray-400">{{ $team->motto }}</p>
            @endif
            <p class="text-xs text-gray-500 mt-4">Nama peserta diisi administrator. Role user hanya melihat dan memilih roster.</p>
        </div>
    </div>

    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-4">PESERTA</h2>
        <ul class="space-y-2 text-sm">
            @forelse ($team->participants as $participant)
                <li class="flex items-center justify-between bg-black/20 rounded-xl px-4 py-3">
                    <span>{{ $participant->name }}</span>
                    @if ($participant->is_captain)
                        <span class="text-[11px] text-brand-cyan">Kapten</span>
                    @endif
                </li>
            @empty
                <li class="text-gray-500 text-sm">Belum ada peserta. Hubungi administrator.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-4">ROSTER PER GAME</h2>
        <div class="space-y-4">
            @foreach ($games as $game)
                <div>
                    <p class="text-xs text-gray-500 mb-2">{{ $game->name }}</p>
                    <p class="text-sm">
                        {{ ($rosters->get($game->id) ?? collect())->pluck('participant.name')->filter()->join(', ') ?: 'Belum dipilih' }}
                    </p>
                </div>
            @endforeach
        </div>
        <a href="{{ route('pic.rosters.edit') }}" class="inline-block mt-6 text-brand-cyan text-sm">Ubah roster</a>
    </div>
</section>
@endsection
