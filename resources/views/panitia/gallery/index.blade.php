@extends('layouts.panitia')

@section('title', 'Gallery')

@section('content')
<section class="space-y-6">
    <div>
        <h1 class="font-gaming text-2xl mb-2">GALLERY</h1>
        <p class="text-xs text-gray-500">Unggah foto slideshow per game. Tampil di halaman publik Gallery.</p>
    </div>

    <div class="space-y-3 max-w-3xl">
        @forelse ($games as $game)
            <a href="{{ route('panitia.gallery.show', $game) }}" class="flex items-center justify-between gap-4 bg-brand-card border border-white/5 rounded-2xl px-5 py-4 hover:border-brand-cyan/40">
                <div>
                    <p class="font-gaming">{{ $game->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $game->is_active ? 'Aktif' : 'Nonaktif' }} · {{ $game->gallery_photos_count }} foto</p>
                </div>
                <span class="text-sm text-brand-cyan">Kelola →</span>
            </a>
        @empty
            <div class="bg-brand-card border border-white/5 rounded-2xl p-6 text-sm text-gray-500">Belum ada game. Buat dulu di Master games.</div>
        @endforelse
    </div>
</section>
@endsection
