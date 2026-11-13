<x-layouts.public :setting="$setting" active="gallery" :title="'Gallery | '.($setting->name ?? 'BedeBest')">
<main class="max-w-6xl mx-auto px-4 pt-28 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">Dokumentasi</p>
    <h1 class="font-gaming text-4xl mb-3 text-brand-gold">GALLERY</h1>
    <p class="text-gray-400 text-sm mb-10">Pilih cabang untuk melihat kolase foto. Klik thumbnail game untuk masuk.</p>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($games as $game)
            @php $cover = $game->galleryPhotos->first(); @endphp
            <a href="{{ route('gallery.show', $game) }}" class="group bg-brand-card border border-white/5 rounded-2xl overflow-hidden hover:border-brand-cyan/40 transition">
                <div class="relative aspect-[16/10] bg-black/40 overflow-hidden">
                    @if ($cover)
                        <img src="{{ $cover->url() }}" alt="{{ $game->name }}" class="absolute inset-0 w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-3 left-4 right-4">
                        <h2 class="font-gaming text-xl">{{ strtoupper($game->name) }}</h2>
                        <p class="text-[11px] uppercase tracking-wider text-brand-cyan mt-1">{{ $game->gallery_photos_count }} foto</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 bg-brand-card border border-white/5 rounded-2xl p-10 text-center">
                <p class="font-gaming text-lg mb-2">BELUM ADA FOTO</p>
                <p class="text-sm text-gray-500">Administrator belum mengunggah gallery untuk game aktif.</p>
            </div>
        @endforelse
    </div>
</main>
</x-layouts.public>
