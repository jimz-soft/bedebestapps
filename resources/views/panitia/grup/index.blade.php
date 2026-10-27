@extends('layouts.panitia')

@section('title', 'Master grup')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="font-gaming text-2xl">MASTER GRUP</h1>
            <p class="text-xs text-gray-500 mt-1">Nama, kode, warna, motto, logo, dan banner grup. Hanya administrator.</p>
        </div>
        <a href="{{ route('panitia.grup.create') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Grup baru</a>
    </div>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-gray-500">
            <tr><th class="text-left py-2">Grup</th><th>Kode</th><th>Urutan</th><th>Peserta</th><th></th></tr>
        </thead>
        <tbody>
            @foreach ($teams as $team)
                <tr class="border-t border-white/5">
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full border-2 shrink-0" style="border-color: {{ $team->color }}; background: {{ $team->color }}33"></span>
                            <div>
                                <p>{{ $team->name }}</p>
                                @if ($team->motto)
                                    <p class="text-[11px] text-gray-500">{{ $team->motto }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center font-gaming">{{ $team->code }}</td>
                    <td class="text-center">{{ $team->sort_order }}</td>
                    <td class="text-center">{{ $team->participants_count }}</td>
                    <td class="text-right space-x-3">
                        <a href="{{ route('panitia.grup.edit', $team) }}" class="text-brand-cyan">Ubah</a>
                        <form method="POST" action="{{ route('panitia.grup.destroy', $team) }}" class="inline" onsubmit="return confirm('Hapus grup {{ $team->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-gray-500 text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
