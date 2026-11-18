@extends('layouts.tim')

@section('title', 'Profil tim')

@section('content')
<section class="space-y-8">
    <div class="bg-brand-card border border-white/5 rounded-2xl overflow-hidden">
        <div class="relative h-56 md:h-72 bg-gradient-to-br from-brand-purple/40 via-brand-dark to-brand-cyan/30">
            <img src="{{ $team->bannerUrl() }}" alt="Banner {{ $team->name }}" class="absolute inset-0 w-full h-full object-cover object-[center_18%]">
            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-brand-card via-brand-card/80 to-transparent"></div>
        </div>
        <div class="relative px-6 pb-6 -mt-12 flex flex-col md:flex-row items-center md:items-end gap-5">
            <x-team-avatar :team="$team" size="w-24 h-24" class="bg-brand-dark shadow-[0_0_0_4px_#12121c]" />
            <div class="text-center md:text-left pb-1">
                <p class="text-[10px] uppercase tracking-widest text-brand-purple mb-2">Profil tim</p>
                <h1 class="font-gaming text-3xl mb-2">{{ strtoupper($team->name) }}</h1>
                <p class="font-gaming text-sm mb-3" style="color: {{ $team->color }}">{{ $team->code }}</p>
                @if ($team->motto)
                    <p class="text-sm text-gray-400">{{ $team->motto }}</p>
                @endif
                <p class="text-xs text-gray-500 mt-4">Nama peserta diisi administrator. Role user hanya melihat dan memilih roster.</p>
            </div>
        </div>
    </div>

    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-4">PESERTA</h2>
        <ul class="space-y-2 text-sm">
            @forelse ($team->participants as $participant)
                <li class="flex items-center justify-between bg-black/20 rounded-xl px-4 py-3">
                    <span>{{ $participant->name }}</span>
                    @if ($participant->leadershipLabel())
                        <span class="text-[11px] text-brand-cyan">{{ $participant->leadershipLabel() }}</span>
                    @endif
                </li>
            @empty
                <li class="text-gray-500 text-sm">Belum ada peserta. Hubungi administrator.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-4">ROSTER PER PERTANDINGAN</h2>
        <div class="space-y-4">
            @forelse ($matches as $match)
                <div>
                    <p class="text-xs text-gray-500 mb-2">{{ $match->rosterLabel() }}</p>
                    <p class="text-sm">
                        {{ ($rosters->get($match->id) ?? collect())->pluck('participant.name')->filter()->join(', ') ?: 'Belum dipilih' }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada jadwal pertandingan yang memakai roster.</p>
            @endforelse
        </div>
        <a href="{{ route('pic.rosters.edit') }}" class="inline-block mt-6 text-brand-cyan text-sm">Ubah roster</a>
    </div>
</section>
@endsection
