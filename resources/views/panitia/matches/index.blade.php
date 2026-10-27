@extends('layouts.panitia')

@section('title', 'Jadwal')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="font-gaming text-2xl">JADWAL</h1>
        <a href="{{ route('panitia.matches.create') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Jadwal baru</a>
    </div>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-gray-500">
            <tr><th class="text-left py-2">Game</th><th>Babak</th><th>Tim</th><th>Waktu</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($matches as $match)
                <tr class="border-t border-white/5">
                    <td class="py-3">{{ $match->game->name }}</td>
                    <td class="text-center">{{ $match->round_label }}</td>
                    <td class="text-center">
                        @foreach ($match->matchTeams as $side)
                            @if (! $loop->first)<span class="text-gray-500"> · </span>@endif
                            <span class="inline-flex items-center gap-1">{{ $side->team->name }} <x-winner-star :side="$side" /></span>
                        @endforeach
                    </td>
                    <td class="text-center">{{ $match->scheduled_at?->format('d M H:i') }}</td>
                    <td class="text-center">{{ $match->status }}</td>
                    <td class="text-right space-x-3">
                        <a href="{{ route('panitia.scores.edit', $match) }}" class="text-brand-cyan">Skor & poin</a>
                        <a href="{{ route('panitia.matches.edit', $match) }}" class="text-gray-400">Ubah</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-white/5"><td colspan="6" class="py-6 text-center text-gray-500">Belum ada jadwal.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
