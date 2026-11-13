@extends('layouts.panitia')

@section('title', 'Peserta')

@section('content')
<section class="space-y-8">
    <div>
        <h1 class="font-gaming text-2xl mb-2">PESERTA & KAPTEN</h1>
        <p class="text-xs text-gray-500">Setiap peserta bisa bermain. Cukup tandai kapten (maksimal dua per grup). Siapa yang main di cabang tertentu dipilih di menu Pilih roster.</p>
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
            <div class="bg-brand-card border border-white/5 rounded-2xl p-4">
                <form method="POST" action="{{ route('panitia.participants.update', $participant) }}" class="grid md:grid-cols-4 gap-3 items-center">
                    @csrf
                    @method('PUT')
                    <input name="name" value="{{ $participant->name }}" class="md:col-span-2">
                    <label class="text-xs flex items-center gap-2"><input type="checkbox" name="is_captain" value="1" class="w-4" @checked($participant->is_captain)> Kapten</label>
                    <button class="text-brand-cyan text-sm justify-self-end">Simpan</button>
                </form>
                <form method="POST" action="{{ route('panitia.participants.destroy', $participant) }}" class="mt-2 text-right">
                    @csrf
                    @method('DELETE')
                    <button class="text-gray-500 text-xs">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
    @error('is_captain')<p class="text-xs text-red-400">{{ $message }}</p>@enderror

    <form method="POST" action="{{ route('panitia.participants.store') }}" enctype="multipart/form-data" class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4">
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

        <label class="text-xs flex items-center gap-2">
            <input type="checkbox" name="is_captain" value="1" class="w-4" @checked(old('is_captain'))>
            Tandai sebagai kapten (hanya jika menambah 1 nama)
        </label>

        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Tambah peserta</button>
    </form>
</section>
@endsection
