@extends('layouts.tim')

@section('title', 'Jadwal')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <h1 class="font-gaming text-2xl mb-2">JADWAL</h1>
    <p class="text-xs text-gray-500 mb-6">Jadwal seluruh cabang. Baris grup {{ $team->name }} ditandai.</p>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-gray-500">
            <tr><th class="text-left py-2">Game</th><th>Babak</th><th>Tim</th><th>Waktu</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($matches as $match)
                @php $mine = $match->matchTeams->contains('team_id', $team->id); @endphp
                <tr class="border-t border-white/5 {{ $mine ? 'bg-brand-cyan/5' : '' }}">
                    <td class="py-3">{{ $match->game->name }}</td>
                    <td class="text-center">{{ $match->round_label }}</td>
                    <td class="text-center">
                        @foreach ($match->matchTeams as $side)
                            @if (! $loop->first)<span class="text-gray-500"> · </span>@endif
                            <span class="inline-flex items-center gap-1">{{ $side->team->name }} <x-winner-star :side="$side" /></span>
                        @endforeach
                    </td>
                    <td class="text-center">{{ $match->scheduled_at?->format('d M H:i') }}</td>
                    <td class="text-center">
                        @if ($match->status === 'live')
                            <span class="text-red-400">Live</span>
                        @elseif ($match->status === 'selesai')
                            Selesai
                        @else
                            Upcoming
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="border-t border-white/5"><td colspan="5" class="py-6 text-center text-gray-500">Belum ada jadwal.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
