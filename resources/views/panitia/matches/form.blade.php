@extends('layouts.panitia')

@section('title', $match->exists ? 'Ubah jadwal' : 'Jadwal baru')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6 max-w-3xl">
    <h1 class="font-gaming text-2xl mb-6">{{ $match->exists ? 'UBAH JADWAL' : 'JADWAL BARU' }}</h1>
    <form method="POST" action="{{ $match->exists ? route('panitia.matches.update', $match) : route('panitia.matches.store') }}" class="space-y-4">
        @csrf
        @if ($match->exists)
            @method('PUT')
        @endif
        <div>
            <label class="text-xs text-gray-500">Game</label>
            <select name="game_id" required>
                @foreach ($games as $game)
                    <option value="{{ $game->id }}" @selected((string) old('game_id', $match->game_id) === (string) $game->id)>{{ $game->name }} · {{ $game->formatLabel() }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-xs text-gray-500">Babak / sesi</label><input name="round_label" value="{{ old('round_label', $match->round_label) }}" required placeholder="Semifinal / Sesi 1"></div>
            <div><label class="text-xs text-gray-500">Waktu</label><input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $match->scheduled_at?->format('Y-m-d\\TH:i')) }}" required></div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-xs text-gray-500">Lokasi</label><input name="location" value="{{ old('location', $match->location) }}"></div>
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select name="status">
                    @foreach (['upcoming' => 'Upcoming', 'live' => 'Live', 'selesai' => 'Selesai'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $match->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-2">Tim peserta (duel: 2 tim, lomba bersama: 2 atau lebih)</p>
            <div class="grid sm:grid-cols-2 gap-2">
                @foreach ($teams as $team)
                    <label class="flex items-center gap-2 bg-black/20 rounded-lg px-3 py-2 text-sm">
                        <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" class="w-4" @checked(in_array($team->id, old('team_ids', $selectedTeamIds), true))>
                        {{ $team->name }}
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500">Catatan</label>
            <textarea name="notes" rows="3">{{ old('notes', $match->notes) }}</textarea>
        </div>
        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan jadwal</button>
    </form>
</section>
@endsection
