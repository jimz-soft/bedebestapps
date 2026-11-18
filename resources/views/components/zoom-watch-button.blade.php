@props(['match'])

@if ($match->hasZoomLink())
    <a
        href="{{ $match->zoom_link }}"
        target="_blank"
        rel="noopener noreferrer"
        {{ $attributes->merge(['class' => 'mt-2.5 inline-flex items-center gap-1.5 pl-1.5 pr-2.5 py-1 rounded-full bg-[#0b5cff] hover:bg-[#0a52e6] text-white shadow-[0_0_12px_rgba(11,92,255,.28)] transition']) }}
        aria-label="Watch on Zoom"
    >
        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/15 border border-white/20">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="2.5" y="6" width="12.5" height="12" rx="2.5" fill="currentColor"/>
                <path d="M16.5 10.2 21 7.4v9.2l-4.5-2.8v-3.6Z" fill="currentColor"/>
            </svg>
        </span>
        <span class="text-[11px] font-semibold tracking-wide whitespace-nowrap">
            <span class="uppercase tracking-[0.08em] text-white/80 font-medium">Watch on</span>
            <span class="ml-1 font-bold">Zoom</span>
        </span>
    </a>
@endif
