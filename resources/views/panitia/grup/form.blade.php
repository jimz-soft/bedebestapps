@extends('layouts.panitia')

@section('title', $team->exists ? 'Ubah grup' : 'Grup baru')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6 max-w-3xl">
    <h1 class="font-gaming text-2xl mb-6">{{ $team->exists ? 'UBAH GRUP' : 'GRUP BARU' }}</h1>
    <form method="POST" action="{{ $team->exists ? route('panitia.grup.update', $team) : route('panitia.grup.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if ($team->exists)
            @method('PUT')
        @endif
        <div>
            <label class="text-xs text-gray-500">Nama grup</label>
            <input name="name" value="{{ old('name', $team->name) }}" required>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-gray-500">Kode (maks. 8)</label>
                <input name="code" value="{{ old('code', $team->code) }}" maxlength="8" required placeholder="ACH">
            </div>
            <div>
                <label class="text-xs text-gray-500">Warna</label>
                <input type="color" name="color" value="{{ old('color', $team->color ?: '#8b5cf6') }}">
            </div>
            <div>
                <label class="text-xs text-gray-500">Urutan tampil</label>
                <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $team->sort_order) }}">
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500">Motto</label>
            <input name="motto" value="{{ old('motto', $team->motto) }}" placeholder="Opsional">
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500">Logo grup</label>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                <p class="text-[11px] text-gray-500 mt-2">Ukuran 400 × 400 px (rasio 1:1). JPG, PNG, atau WebP.</p>
                @if ($team->logoUrl())
                    <img src="{{ $team->logoUrl() }}" alt="Logo {{ $team->name }}" class="mt-3 w-16 h-16 rounded-full object-cover border border-white/10">
                @endif
            </div>
            <div>
                <label class="text-xs text-gray-500">Banner grup</label>
                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp">
                <p class="text-[11px] text-gray-500 mt-2">Ukuran 800 × 1067 px (rasio 3:4). Tampil di beranda.</p>
                @if ($team->exists)
                    <img src="{{ $team->bannerUrl() }}" alt="Banner {{ $team->name }}" class="mt-3 w-full h-40 object-cover object-top rounded-xl border border-brand-gold/40">
                @endif
            </div>
        </div>
        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan grup</button>
    </form>
</section>
@endsection
