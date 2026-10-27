@extends($layout)

@section('title', 'Pilih roster')

@section('content')
<section class="space-y-6">
    <div>
        <h1 class="font-gaming text-2xl mb-2">PILIH ROSTER</h1>
        <p class="text-xs text-gray-500">Pilih pemain dari peserta grup untuk game yang memakai roster. Bisa diubah selama pertandingan cabang ini belum selesai. Nama peserta diisi administrator.</p>
    </div>

    <form method="GET" class="grid md:grid-cols-2 gap-4 max-w-3xl">
        <div>
            <label class="text-xs text-gray-500">Game (roster on)</label>
            <select name="game_id" onchange="this.form.submit()">
                @foreach ($games as $item)
                    <option value="{{ $item->id }}" @selected($item->id === $game->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500">Grup</label>
            @if ($canPickTeam)
                <select name="team_id" onchange="this.form.submit()">
                    @foreach ($teams as $item)
                        <option value="{{ $item->id }}" @selected($item->id === $team->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            @else
                <input value="{{ $team->name }}" readonly>
            @endif
        </div>
    </form>

    @if ($locked)
        <p class="text-sm text-brand-gold">Roster terkunci. Semua pertandingan {{ $game->name }} untuk {{ $team->name }} sudah selesai.</p>
    @endif

    <form method="POST" action="{{ $formAction }}" class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="game_id" value="{{ $game->id }}">
        <input type="hidden" name="team_id" value="{{ $team->id }}">

        @forelse ($participants as $participant)
            <label class="flex items-center gap-3 bg-black/20 rounded-xl px-4 py-3 {{ $locked ? 'opacity-60' : '' }}">
                <input type="checkbox" name="participant_ids[]" value="{{ $participant->id }}" class="w-4" @checked(in_array($participant->id, $selectedIds, true)) @disabled($locked)>
                <span>{{ $participant->name }}</span>
                @if ($participant->is_captain)
                    <span class="text-[11px] text-gray-500">Kapten</span>
                @endif
            </label>
        @empty
            <p class="text-sm text-gray-500">Belum ada peserta di grup ini. Administrator mengisi nama di menu Peserta & kapten, baru roster bisa dipilih.</p>
        @endforelse

        @if ($participants->isNotEmpty() && ! $locked)
            <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan roster</button>
        @endif
    </form>
</section>
@endsection
