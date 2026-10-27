<x-layouts.public :setting="$setting" :active="$active" :title="$title.' | '.($setting->name ?? 'BedeBest')">
<main class="max-w-5xl mx-auto px-4 pt-32 pb-20">
    <p class="text-brand-cyan uppercase tracking-widest text-xs mb-2">BedeBest</p>
    <h1 class="font-gaming text-4xl mb-4 text-brand-gold">{{ strtoupper($title) }}</h1>
    <p class="text-gray-400 text-sm">Halaman ini menyusul. Data dan tampilan akan mengikuti mockup setelah banner beranda selesai dipakai.</p>
</main>
</x-layouts.public>
