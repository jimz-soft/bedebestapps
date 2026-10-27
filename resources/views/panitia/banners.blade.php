@extends('layouts.panitia')

@section('title', 'Banner beranda')

@section('content')
<form method="POST" action="{{ route('panitia.banners.update') }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method('PUT')

    <section class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-2">BANNER HALAMAN UTAMA</h2>
        <p class="text-xs text-gray-500 mb-4">Foto ini menjadi latar hero di beranda. Hanya panitia yang bisa mengganti.</p>
        <label class="text-xs text-gray-500">Banner halaman utama</label>
        <input type="file" name="hero_banner" accept="image/jpeg,image/png,image/webp">
        <p class="text-[11px] text-gray-500 mt-2">Ukuran 1920 × 1080 px (rasio 16:9). JPG, PNG, atau WebP.</p>
        @error('hero_banner')
            <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
        @enderror
        <div class="mt-3 h-32 rounded-xl border border-brand-gold/40 bg-cover bg-center" style="background-image:url('{{ $setting->heroBannerUrl() ?? 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=2070&auto=format&fit=crop' }}')"></div>
    </section>

    <section class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-2">BANNER GRUP DI BERANDA</h2>
        <p class="text-xs text-gray-500 mb-5">Empat foto ini tampil di halaman utama, tepat di atas papan skor.</p>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($teams as $team)
                <div>
                    <img src="{{ $team->bannerUrl() }}" alt="Banner {{ $team->name }}" class="w-full h-44 object-cover object-top rounded-xl border border-brand-gold/40 mb-3">
                    <label class="text-xs text-gray-500">Banner {{ $team->name }}</label>
                    <input type="file" name="team_banners[{{ $team->id }}]" accept="image/jpeg,image/png,image/webp">
                    <p class="text-[11px] text-gray-500 mt-2">Ukuran 800 × 1067 px (rasio 3:4). JPG, PNG, atau WebP.</p>
                    @error('team_banners.'.$team->id)
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-brand-card border border-white/5 rounded-2xl p-6">
        <h2 class="font-gaming mb-2">BANNER GAMES</h2>
        <p class="text-xs text-gray-500 mb-5">Foto 16:9 ini tampil di halaman Games publik, di atas peraturan masing-masing cabang.</p>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($games as $game)
                <div>
                    <div class="w-full h-36 rounded-xl border border-brand-gold/40 mb-3 bg-cover bg-center bg-gradient-to-br from-brand-purple/40 to-brand-cyan/30" @if ($game->bannerUrl()) style="background-image:url('{{ $game->bannerUrl() }}')" @endif></div>
                    <label class="text-xs text-gray-500">Banner {{ $game->name }}</label>
                    <input type="file" name="game_banners[{{ $game->id }}]" accept="image/jpeg,image/png,image/webp">
                    <p class="text-[11px] text-gray-500 mt-2">Ukuran 1280 × 720 px (rasio 16:9). JPG, PNG, atau WebP.</p>
                    @error('game_banners.'.$game->id)
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>

    <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan banner</button>
</form>
@endsection
