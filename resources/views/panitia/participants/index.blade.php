@extends('layouts.panitia')

@section('title', 'Peserta')

@section('content')
<section class="space-y-8">
    <div>
        <h1 class="font-gaming text-2xl mb-2">PESERTA & CAPTAIN</h1>
        <p class="text-xs text-gray-500">Setiap peserta bisa bermain. Per tim: 1 Captain dan 1 Vice Captain. Hanya panitia yang bisa mengatur. Siapa yang main di cabang tertentu dipilih di menu Pilih roster.</p>
    </div>
    <form method="GET" class="max-w-xs">
        <label class="text-xs text-gray-500">Pilih tim</label>
        <select name="team_id" onchange="this.form.submit()">
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected((string) $teamId === (string) $team->id)>{{ $team->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="space-y-3">
        @foreach ($participants as $participant)
            @php
                $currentLeadership = $participant->is_captain ? 'captain' : ($participant->is_co_captain ? 'co_captain' : '');
            @endphp
            <div class="bg-brand-card border border-white/5 rounded-2xl p-4">
                <form method="POST" action="{{ route('panitia.participants.update', $participant) }}" class="grid md:grid-cols-4 gap-3 items-center" data-confirm-title="Simpan peserta" data-confirm="Perubahan nama/peran peserta akan disimpan. Lanjutkan?" data-confirm-action="Ya, simpan">
                    @csrf
                    @method('PUT')
                    <input name="name" value="{{ $participant->name }}" class="md:col-span-2">
                    <div>
                        <label class="text-xs text-gray-500">Peran</label>
                        <select name="leadership">
                            <option value="" @selected($currentLeadership === '')>Peserta</option>
                            <option value="captain" @selected($currentLeadership === 'captain')>Captain</option>
                            <option value="co_captain" @selected($currentLeadership === 'co_captain')>Vice Captain</option>
                        </select>
                    </div>
                    <button class="text-brand-cyan text-sm justify-self-end">Simpan</button>
                </form>
                <form method="POST" action="{{ route('panitia.participants.destroy', $participant) }}" class="mt-2 text-right" data-confirm-title="Hapus peserta" data-confirm="Peserta ini akan dihapus dari tim. Lanjutkan?" data-confirm-action="Ya, hapus" data-confirm-tone="danger">
                    @csrf
                    @method('DELETE')
                    <button class="text-gray-500 text-xs">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
    @error('leadership')<p class="text-xs text-red-400">{{ $message }}</p>@enderror

    <form method="POST" action="{{ route('panitia.participants.store') }}" enctype="multipart/form-data" class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4" data-confirm-title="Tambah peserta" data-confirm="Peserta baru akan ditambahkan ke tim ini. Lanjutkan?" data-confirm-action="Ya, tambah">
        @csrf
        <input type="hidden" name="team_id" value="{{ $teamId }}">

        <div>
            <label class="text-xs text-gray-500">Nama peserta (bisa banyak sekaligus)</label>
            <textarea name="names" rows="8" placeholder="Satu baris satu nama&#10;Andika Pratama&#10;Budi Santoso&#10;Citra Lestari">{{ old('names') }}</textarea>
            <p class="text-[11px] text-gray-500 mt-2">Tempel banyak nama sekaligus: satu baris satu nama. Nama ganda di grup yang sama akan dilewati.</p>
            @error('names')
                <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
            @enderror
            @error('name')
                <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="text-xs text-gray-500">Atau import dari file (.txt / .csv)</label>
            <input type="file" name="import_file" accept=".txt,.csv,text/plain,text/csv">
            <p class="text-[11px] text-gray-500 mt-2">Format CSV: kolom pertama = nama. Baris header `Nama` / `Name` boleh ada.</p>
            @error('import_file')
                <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="max-w-xs">
            <label class="text-xs text-gray-500">Peran (hanya jika menambah 1 nama)</label>
            <select name="leadership">
                <option value="" @selected(old('leadership') === null || old('leadership') === '')>Peserta</option>
                <option value="captain" @selected(old('leadership') === 'captain')>Captain</option>
                <option value="co_captain" @selected(old('leadership') === 'co_captain')>Vice Captain</option>
            </select>
        </div>

        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Tambah peserta</button>
    </form>
</section>
@endsection
