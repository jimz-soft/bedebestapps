<x-layouts.public :setting="$setting" active="tim" :title="$team->name.' | '.($setting->name ?? 'BedeBest')">
<main class="max-w-5xl mx-auto px-4 pt-28 pb-20">
    @php
        $rankColor = match ($rank) {
            1 => 'text-brand-gold',
            2 => 'text-[#c0c7d1]',
            3 => 'text-[#cd7f32]',
            default => 'text-white',
        };
        $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    @endphp

    <section class="mb-10 overflow-hidden rounded-2xl border border-white/5 bg-brand-card">
        <div class="relative h-52 md:h-72 bg-gradient-to-br from-brand-purple/40 via-brand-dark to-brand-cyan/30">
            <img src="{{ $team->bannerUrl() }}" alt="Banner {{ $team->name }}" class="absolute inset-0 w-full h-full object-cover object-[center_18%]">
            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-brand-card via-brand-card/80 to-transparent"></div>
        </div>
        <div class="relative px-6 pb-6 -mt-14 flex flex-col md:flex-row items-center md:items-end gap-6">
            <x-team-avatar :team="$team" size="w-28 h-28" class="bg-brand-dark shadow-[0_0_0_4px_#12121c]" />
            <div class="text-center md:text-left pb-1">
                <h1 class="font-gaming text-4xl text-brand-gold">{{ strtoupper($team->name) }}</h1>
                <p class="text-gray-400 mt-2">Peringkat <span class="font-gaming {{ $rankColor }}">#{{ $rank }}</span> · {{ $team->participants->count() }} peserta · {{ $captains->count() }} kapten</p>
                @if ($captains->isNotEmpty())
                    <p class="text-sm text-gray-500 mt-1">Kapten: {{ $captains->pluck('name')->join(' · ') }}</p>
                @endif
                <div class="flex gap-6 mt-4 justify-center md:justify-start text-sm">
                    <div>
                        <p class="font-gaming text-2xl text-brand-cyan">{{ $total }}</p>
                        <p class="text-gray-500 text-xs uppercase">Total</p>
                    </div>
                    <div>
                        <p class="font-gaming text-2xl">{{ $poin_games }}</p>
                        <p class="text-gray-500 text-xs uppercase">Poin games</p>
                    </div>
                    <div>
                        <p class="font-gaming text-2xl">{{ $poin_personil }}</p>
                        <p class="text-gray-500 text-xs uppercase">Poin personil</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <h2 class="font-gaming uppercase mb-2">Daftar peserta</h2>
    <p class="text-xs text-gray-500 mb-4">Diisi hanya oleh panitia. Kapten ditandai administrator.</p>
    <ul class="bg-brand-card border border-white/5 rounded-2xl divide-y divide-white/5 mb-10">
        @forelse ($team->participants as $participant)
            <li class="px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                <span>{{ $participant->name }}</span>
                @if ($participant->is_captain)
                    <span class="text-[10px] uppercase px-2 py-0.5 rounded border border-brand-purple/50 text-brand-purple">Kapten</span>
                @endif
            </li>
        @empty
            <li class="px-5 py-6 text-sm text-gray-500">Belum ada peserta.</li>
        @endforelse
    </ul>

    @if ($rosterGames->isNotEmpty())
        <h2 class="font-gaming uppercase mb-2">Roster per game</h2>
        <p class="text-xs text-gray-500 mb-4">Dipilih PIC atau panitia dari daftar peserta. Game dengan roster off tidak ditampilkan.</p>
        <div class="grid md:grid-cols-2 gap-4 mb-10">
            @foreach ($rosterGames as $game)
                @php $entries = $rosters->get($game->id) ?? collect(); @endphp
                <div class="bg-brand-card border border-white/5 rounded-2xl p-5">
                    <p class="font-gaming text-sm mb-3">{{ $game->name }} <span class="text-[10px] text-brand-cyan">On</span></p>
                    <p class="text-sm text-gray-300">
                        @forelse ($entries as $entry)
                            {{ $entry->participant?->name }}@if ($entry->participant?->is_captain) <span class="text-[10px] text-brand-purple">K</span>@endif @if (! $loop->last)·@endif
                        @empty
                            Belum dipilih
                        @endforelse
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="font-gaming uppercase mb-4">Pertandingan {{ $team->name }}</h2>
    <div class="space-y-3">
        @forelse ($matches as $match)
            @php
                $duel = $match->isDuel();
                $self = $match->matchTeams->firstWhere('team_id', $team->id);
                $opponent = $match->matchTeams->first(fn ($side) => $side->team_id !== $team->id);
                $showScore = in_array($match->status, ['live', 'selesai'], true);
                $when = $match->scheduled_at
                    ? $match->scheduled_at->format('j').' '.$months[$match->scheduled_at->month].' '.$match->scheduled_at->format('H:i')
                    : ($match->round_label ?: '');
            @endphp
            @if ($match->status === 'live' && $duel)
                <div class="bg-brand-card border border-red-500/30 rounded-xl p-4 flex justify-between items-center">
                    <span class="text-xs text-red-400 uppercase flex items-center gap-2"><span class="w-2 h-2 bg-red-500 rounded-full live-dot"></span> Live vs {{ $opponent?->team->name }}</span>
                    <span class="font-gaming text-brand-cyan">{{ $self->score_cache ?? 0 }} : {{ $opponent->score_cache ?? 0 }}</span>
                </div>
            @elseif ($duel)
                <div class="bg-brand-card border border-white/5 rounded-xl p-4 flex justify-between text-sm text-gray-400">
                    <span class="inline-flex items-center gap-1">{{ $match->game->name }} vs {{ $opponent?->team->name }} · {{ $when }} @if ($self)<x-winner-star :side="$self" />@endif</span>
                    @if ($showScore)
                        <span class="font-gaming text-brand-cyan">{{ $self->score_cache ?? 0 }} : {{ $opponent->score_cache ?? 0 }}</span>
                    @else
                        <span>{{ $match->status === 'selesai' ? 'Selesai' : 'Upcoming' }}</span>
                    @endif
                </div>
            @else
                <div class="bg-brand-card border {{ $match->status === 'live' ? 'border-red-500/30' : 'border-white/5' }} rounded-xl p-4 flex justify-between text-sm text-gray-400">
                    <span class="flex items-center gap-2">
                        @if ($match->status === 'live')
                            <span class="w-2 h-2 bg-red-500 rounded-full live-dot"></span>
                        @endif
                        {{ $match->game->name }} {{ $match->round_label }}@if ($match->scheduled_at) · {{ $match->scheduled_at->format('H:i') }}@endif
                    </span>
                    <span>{{ $match->matchTeams->count() }} grup</span>
                </div>
            @endif
        @empty
            <div class="bg-brand-card border border-white/5 rounded-xl p-6 text-sm text-gray-500">Belum ada pertandingan untuk grup ini.</div>
        @endforelse
    </div>
</main>
</x-layouts.public>
