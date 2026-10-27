@props(['side'])

@if ($side->isWinner())
    <span {{ $attributes->merge(['class' => 'text-brand-gold', 'title' => 'Pemenang']) }} aria-label="Pemenang">★</span>
@endif
