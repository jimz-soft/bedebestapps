@extends('layouts.panitia')

@section('title', 'Skor & poin')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <h1 class="font-gaming text-2xl mb-2">SKOR & POIN</h1>
    <p class="text-xs text-gray-500 mb-6">Isi skor babak, poin games, poin personil, dan bonus pemenang (wajib ada keterangan).</p>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-gray-500">
            <tr><th class="text-left py-2">Game</th><th>Babak</th><th>Tim</th><th>Status</th><th></th></tr>
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
                    <td class="text-center">{{ $match->status }}</td>
                    <td class="text-right"><a href="{{ route('panitia.scores.edit', $match) }}" class="text-brand-cyan">Input skor</a></td>
                </tr>
            @empty
                <tr class="border-t border-white/5"><td colspan="5" class="py-6 text-center text-gray-500">Buat jadwal dulu sebelum mengisi skor.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
