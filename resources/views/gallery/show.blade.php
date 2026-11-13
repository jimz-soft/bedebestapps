<x-layouts.public :setting="$setting" active="gallery" :title="$game->name.' Gallery | '.($setting->name ?? 'BedeBest')">
<main class="max-w-6xl mx-auto px-4 pt-28 pb-20">
    <a href="{{ route('gallery') }}" class="text-xs text-brand-cyan uppercase tracking-wider">← Semua gallery</a>
    <p class="text-brand-cyan uppercase tracking-widest text-xs mt-4 mb-2">Kolase foto</p>
    <h1 class="font-gaming text-4xl mb-2 text-brand-gold">{{ strtoupper($game->name) }}</h1>
    <p class="text-gray-400 text-sm mb-10">{{ $game->galleryPhotos->count() }} foto · klik foto untuk memperbesar</p>

    <div class="columns-1 sm:columns-2 lg:columns-3 gap-4 space-y-4" id="gallery-grid">
        @foreach ($game->galleryPhotos as $photo)
            <article class="break-inside-avoid group relative overflow-hidden rounded-2xl border border-white/5 bg-brand-card">
                <button
                    type="button"
                    class="gallery-open block w-full text-left"
                    data-src="{{ $photo->url() }}"
                    data-caption="{{ $photo->caption ?: $game->name }}"
                    data-download="{{ route('gallery.download', $photo) }}"
                    data-index="{{ $loop->index }}"
                >
                    <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?: $game->name }}" class="w-full h-auto object-cover transition duration-300 group-hover:scale-[1.02]">
                </button>
                <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 bg-gradient-to-t from-black/85 via-black/40 to-transparent p-4 pointer-events-none">
                    <p class="text-xs text-gray-200 min-w-0 truncate">{{ $photo->caption ?: $game->name }}</p>
                    <a
                        href="{{ route('gallery.download', $photo) }}"
                        class="pointer-events-auto shrink-0 rounded-full border border-brand-gold/50 bg-black/50 px-3 py-1.5 text-[10px] uppercase tracking-wider text-brand-gold hover:border-brand-cyan hover:text-brand-cyan"
                        download
                    >Download</a>
                </div>
            </article>
        @endforeach
    </div>
</main>

<div id="gallery-lightbox" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/90 p-4" role="dialog" aria-modal="true" aria-label="Pratinjau foto">
    <button type="button" id="lightbox-close" class="absolute top-4 right-4 z-10 w-11 h-11 rounded-full border border-white/20 text-brand-gold hover:border-brand-cyan" aria-label="Tutup">✕</button>
    <button type="button" id="lightbox-prev" class="absolute left-3 md:left-6 z-10 w-11 h-11 rounded-full border border-white/20 text-brand-gold hover:border-brand-cyan" aria-label="Sebelumnya">‹</button>
    <button type="button" id="lightbox-next" class="absolute right-3 md:right-6 z-10 w-11 h-11 rounded-full border border-white/20 text-brand-gold hover:border-brand-cyan" aria-label="Berikutnya">›</button>

    <div class="relative max-w-5xl w-full text-center">
        <img id="lightbox-image" src="" alt="" class="max-h-[78vh] w-auto max-w-full mx-auto rounded-xl object-contain shadow-[0_0_40px_rgba(0,0,0,.6)]">
        <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
            <p id="lightbox-caption" class="text-sm text-gray-300"></p>
            <a id="lightbox-download" href="#" class="rounded-full border border-brand-gold/50 px-4 py-2 text-xs uppercase tracking-wider text-brand-gold hover:border-brand-cyan hover:text-brand-cyan" download>Download</a>
        </div>
    </div>
</div>

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.gallery-open')).map(function (btn) {
        return {
            src: btn.dataset.src,
            caption: btn.dataset.caption || '',
            download: btn.dataset.download || '#',
        };
    });
    if (!items.length) return;

    const box = document.getElementById('gallery-lightbox');
    const image = document.getElementById('lightbox-image');
    const caption = document.getElementById('lightbox-caption');
    const download = document.getElementById('lightbox-download');
    const btnClose = document.getElementById('lightbox-close');
    const btnPrev = document.getElementById('lightbox-prev');
    const btnNext = document.getElementById('lightbox-next');
    let index = 0;

    function show(i) {
        index = (i + items.length) % items.length;
        const item = items[index];
        image.src = item.src;
        image.alt = item.caption;
        caption.textContent = item.caption;
        download.href = item.download;
        box.classList.remove('hidden');
        box.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function hide() {
        box.classList.add('hidden');
        box.classList.remove('flex');
        document.body.style.overflow = '';
        image.src = '';
    }

    document.querySelectorAll('.gallery-open').forEach(function (btn) {
        btn.addEventListener('click', function () {
            show(Number(btn.dataset.index || 0));
        });
    });

    btnClose.addEventListener('click', hide);
    btnPrev.addEventListener('click', function () { show(index - 1); });
    btnNext.addEventListener('click', function () { show(index + 1); });
    box.addEventListener('click', function (event) {
        if (event.target === box) hide();
    });
    document.addEventListener('keydown', function (event) {
        if (box.classList.contains('hidden')) return;
        if (event.key === 'Escape') hide();
        if (event.key === 'ArrowLeft') show(index - 1);
        if (event.key === 'ArrowRight') show(index + 1);
    });
})();
</script>
</x-layouts.public>
