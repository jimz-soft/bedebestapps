@extends('layouts.panitia')

@section('title', 'Master games')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="font-gaming text-2xl">MASTER GAMES</h1>
        <a href="{{ route('panitia.games.create') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Game baru</a>
    </div>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-gray-500">
            <tr><th class="text-left py-2">Game</th><th>Format</th><th>Roster</th><th>Skor babak</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @foreach ($games as $game)
                <tr class="border-t border-white/5">
                    <td class="py-3">{{ $game->name }}</td>
                    <td class="text-center">{{ $game->formatLabel() }}</td>
                    <td class="text-center">{{ $game->requires_roster ? 'On' : 'Off' }}</td>
                    <td class="text-center">{{ $game->uses_period_score ? ($game->period_label ?: 'Ya') : '—' }}</td>
                    <td class="text-center">{{ $game->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="text-right">
                        <a href="{{ route('panitia.games.edit', $game) }}" class="text-brand-cyan">Ubah</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
