@extends('layouts.panitia')

@section('title', 'Gallery '.$game->name)

@section('content')
<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('panitia.gallery.index') }}" class="text-xs text-brand-cyan">← Semua game</a>
            <h1 class="font-gaming text-2xl mt-2">GALLERY · {{ strtoupper($game->name) }}</h1>
            <p class="text-xs text-gray-500 mt-1">JPG/PNG/WebP, maks 5 MB per foto. Bisa unggah beberapa sekaligus.</p>
        </div>
        <a href="{{ route('gallery.show', $game) }}" target="_blank" class="text-sm text-brand-cyan">Lihat di publik →</a>
    </div>

    <form method="POST" action="{{ route('panitia.gallery.store', $game) }}" enctype="multipart/form-data" class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4 max-w-3xl" data-confirm-title="Unggah foto" data-confirm="Foto terpilih akan diunggah ke gallery game ini dan tampil di situs publik. Lanjutkan?" data-confirm-action="Ya, unggah">
        @csrf
        <div>
            <label class="text-xs text-gray-500">Foto</label>
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>
        </div>
        <button type="submit" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Unggah foto</button>
    </form>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($game->galleryPhotos as $photo)
            <article class="bg-brand-card border border-white/5 rounded-2xl overflow-hidden">
                <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?: $game->name }}" class="w-full aspect-video object-cover">
                <div class="p-4 flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-500 truncate">{{ $photo->caption ?: 'Tanpa caption' }}</p>
                    <form method="POST" action="{{ route('panitia.gallery.destroy', [$game, $photo]) }}" data-confirm-title="Hapus foto" data-confirm="Foto ini akan dihapus permanen dari gallery. Lanjutkan?" data-confirm-action="Ya, hapus" data-confirm-tone="danger">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-300">Hapus</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 bg-brand-card border border-white/5 rounded-2xl p-6 text-sm text-gray-500">Belum ada foto untuk game ini.</div>
        @endforelse
    </div>
</section>
@endsection
