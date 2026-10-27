@props(['team', 'size' => 'w-16 h-16', 'tinted' => true])

@php
    $logo = $team->logoUrl();
    $classes = $size.' rounded-full border-2 shrink-0';
    $border = $tinted ? 'border-color: '.$team->color : null;
@endphp

@if ($logo)
    <img
        src="{{ $logo }}"
        alt="{{ $team->name }}"
        {{ $attributes->merge(['class' => $classes.' object-cover']) }}
        @if ($border) style="{{ $border }}" @endif
    >
@else
    <span
        {{ $attributes->merge(['class' => $classes.' inline-flex items-center justify-center font-gaming text-[11px]']) }}
        @if ($border) style="{{ $border }}" @endif
    >{{ $team->code }}</span>
@endif
