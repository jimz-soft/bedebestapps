@extends('layouts.panitia')

@section('title', 'Skor & poin')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <h1 class="font-gaming text-2xl mb-2">INPUT SKOR & POIN</h1>
    <p class="text-sm text-gray-400 mb-2">{{ $match->game->name }} · {{ $match->round_label }} · {{ $match->matchTeams->pluck('team.name')->join(' vs ') }}</p>
    <p class="text-xs text-gray-500 mb-6">Simpan skor agar tampil di beranda. Status Upcoming otomatis jadi Live. Poin personil dan bonus pemenang ikut tersimpan lewat tombol simpan atau flag selesai. Pertandingan yang sudah selesai tetap bisa diubah poinnya.</p>
    <form method="POST" action="{{ route('panitia.scores.update', $match) }}" class="space-y-8">
        @csrf
        @method('PUT')
        <div class="max-w-xs">
            <label class="text-xs text-gray-500">Status pertandingan</label>
            <select name="status">
                @foreach (['upcoming' => 'Upcoming', 'live' => 'Live', 'selesai' => 'Selesai'] as $value => $label)
                    <option value="{{ $value }}" @selected($match->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if ($match->game->uses_period_score)
            <div>
                <h2 class="font-gaming mb-3">Skor per {{ strtolower($match->game->period_label ?: 'babak') }}</h2>
                @foreach ($match->periods->sortBy('period_no') as $period)
                    <div class="grid md:grid-cols-4 gap-4 mb-4 items-end">
                        <input type="hidden" name="periods[{{ $period->id }}][id]" value="{{ $period->id }}">
                        <div>
                            <label class="text-xs text-gray-500">{{ $match->game->period_label ?: 'Babak' }} {{ $period->period_no }}</label>
                            <select name="periods[{{ $period->id }}][status]">
                                @foreach (['pending' => 'Belum', 'running' => 'Berjalan', 'done' => 'Selesai'] as $value => $label)
                                    <option value="{{ $value }}" @selected($period->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @foreach ($match->matchTeams as $side)
                            @php $score = $period->scores->firstWhere('team_id', $side->team_id)?->score ?? 0; @endphp
                            <div>
                                <label class="text-xs text-gray-500">{{ $side->team->name }}</label>
                                <input type="number" min="0" name="periods[{{ $period->id }}][scores][{{ $side->team_id }}]" value="{{ $score }}">
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        <div>
            <h2 class="font-gaming mb-3">Poin games, personil, dan bonus</h2>
            @foreach ($match->matchTeams as $side)
                <div class="bg-black/20 rounded-xl p-4 mb-4 {{ $side->isWinner() ? 'border border-brand-gold/30' : '' }}">
                    <p class="font-gaming mb-3 inline-flex items-center gap-1">{{ $side->team->name }} <x-winner-star :side="$side" /></p>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div><label class="text-xs text-gray-500">Poin games</label><input type="number" min="0" name="teams[{{ $side->id }}][points_awarded]" value="{{ old('teams.'.$side->id.'.points_awarded', $side->points_awarded) }}"></div>
                        <div><label class="text-xs text-gray-500">Poin personil</label><input type="number" min="0" name="teams[{{ $side->id }}][poin_personil]" value="{{ old('teams.'.$side->id.'.poin_personil', $side->poin_personil) }}"></div>
                        <div><label class="text-xs text-gray-500">Bonus pemenang</label><input type="number" min="0" name="teams[{{ $side->id }}][bonus_pemenang]" value="{{ old('teams.'.$side->id.'.bonus_pemenang', $side->bonus_pemenang) }}"></div>
                        <div>
                            <label class="text-xs text-gray-500">Hasil</label>
                            <select name="teams[{{ $side->id }}][result]">
                                <option value="">—</option>
                                @foreach (['win' => 'Menang', 'draw' => 'Seri', 'loss' => 'Kalah'] as $value => $label)
                                    <option value="{{ $value }}" @selected($side->result === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-xs text-gray-500">Keterangan bonus <span class="text-gray-600">(wajib jika bonus &gt; 0)</span></label>
                        <input name="teams[{{ $side->id }}][bonus_keterangan]" value="{{ old('teams.'.$side->id.'.bonus_keterangan', $side->bonus_keterangan) }}" placeholder="Contoh: Bonus semifinal Mini Soccer" class="{{ $errors->has('teams.'.$side->id.'.bonus_keterangan') ? 'input-error' : '' }}">
                        @error('teams.'.$side->id.'.bonus_keterangan')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endforeach
            <p class="text-[11px] text-gray-500">Keterangan wajib jika bonus lebih dari 0. Poin ranking diketik di sini, bukan dari master game.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button class="px-5 py-2 rounded-full bg-white/10 text-sm">Simpan skor & poin</button>
            <button name="intent" value="finish" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Flag pertandingan selesai</button>
        </div>
    </form>

    @if ($match->game->uses_period_score)
        <form method="POST" action="{{ route('panitia.scores.period', $match) }}" class="mt-6">
            @csrf
            <button class="px-5 py-2 rounded-full bg-white/10 text-sm">Tambah babak</button>
        </form>
    @endif
</section>
@endsection
