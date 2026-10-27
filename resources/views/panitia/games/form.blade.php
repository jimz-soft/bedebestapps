@extends('layouts.panitia')

@section('title', $game->exists ? 'Ubah game' : 'Game baru')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6 max-w-3xl">
    <h1 class="font-gaming text-2xl mb-6">{{ $game->exists ? 'UBAH GAME' : 'GAME BARU' }}</h1>
    <form method="POST" action="{{ $game->exists ? route('panitia.games.update', $game) : route('panitia.games.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if ($game->exists)
            @method('PUT')
        @endif
        <div>
            <label class="text-xs text-gray-500">Nama game</label>
            <input name="name" value="{{ old('name', $game->name) }}" required>
        </div>
        <label class="text-xs text-gray-400 flex items-center gap-2"><input type="checkbox" name="is_active" value="1" class="w-4" @checked(old('is_active', $game->is_active ?? true))> Game aktif</label>
        <div>
            <label class="text-xs text-gray-500">Format</label>
            <select name="format">
                <option value="head_to_head" @selected(old('format', $game->format) === 'head_to_head')>Head-to-head / gugur</option>
                <option value="round_robin" @selected(old('format', $game->format) === 'round_robin')>Round robin</option>
                <option value="heat" @selected(old('format', $game->format) === 'heat')>Lomba bersama</option>
            </select>
        </div>
        <label class="text-xs text-gray-400 flex items-center gap-2"><input type="checkbox" name="requires_roster" value="1" class="w-4" @checked(old('requires_roster', $game->requires_roster))> Perlu roster (muncul di menu Pilih roster)</label>
        <label class="text-xs text-gray-400 flex items-center gap-2"><input type="checkbox" name="uses_period_score" value="1" class="w-4" @checked(old('uses_period_score', $game->uses_period_score))> Pakai skor babak</label>
        <div class="grid md:grid-cols-3 gap-4">
            <div><label class="text-xs text-gray-500">Label babak</label><input name="period_label" value="{{ old('period_label', $game->period_label) }}" placeholder="Babak / Set"></div>
            <div><label class="text-xs text-gray-500">Jumlah babak</label><input type="number" name="period_count_default" value="{{ old('period_count_default', $game->period_count_default) }}" min="1" max="9"></div>
            <div>
                <label class="text-xs text-gray-500">Mode skor</label>
                <select name="score_mode">
                    <option value="aggregate" @selected(old('score_mode', $game->score_mode) === 'aggregate')>Akumulasi</option>
                    <option value="sets" @selected(old('score_mode', $game->score_mode) === 'sets')>Set dimenangkan</option>
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500">Jumlah tim per sesi (lomba bersama)</label>
            <input type="number" name="heat_size" value="{{ old('heat_size', $game->heat_size) }}" min="2" max="16">
        </div>
        <div>
            <label class="text-xs text-gray-500">Peraturan</label>
            <textarea name="rules_html" id="rules_html" rows="8">{{ old('rules_html', $game->rules_html) }}</textarea>
            <p class="text-[11px] text-gray-500 mt-2">Isi peraturan dengan editor HTML. Tampil di halaman games publik.</p>
        </div>
        <div>
            <label class="text-xs text-gray-500">Banner game</label>
            <input type="file" name="banner" accept="image/jpeg,image/png,image/webp">
            <p class="text-[11px] text-gray-500 mt-2">Ukuran 1280 × 720 px (rasio 16:9). Tampil di halaman Games publik. JPG, PNG, atau WebP.</p>
            @if ($game->bannerUrl())
                <img src="{{ $game->bannerUrl() }}" alt="Banner {{ $game->name }}" class="mt-3 w-full h-40 object-cover rounded-xl border border-brand-gold/40">
            @endif
        </div>
        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan game</button>
    </form>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#rules_html',
        height: 280,
        menubar: false,
        plugins: 'lists link',
        toolbar: 'bold italic underline | bullist numlist | link | removeformat',
        skin: 'oxide-dark',
        content_css: 'dark',
        branding: false
    });
    document.querySelector('form').addEventListener('submit', function () { tinymce.triggerSave(); });
</script>
@endsection
