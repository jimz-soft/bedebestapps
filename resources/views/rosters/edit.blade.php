@extends($layout)

@section('title', 'Pilih roster')

@section('content')
@php
    $rosterMin = (int) ($game->roster_min ?? 0);
    $rosterMax = (int) ($game->roster_max ?? 50);
@endphp
<style>
    .roster-check {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
</style>
<section class="space-y-6">
    <div>
        <h1 class="font-gaming text-2xl mb-2">PILIH ROSTER</h1>
        @if ($canPickTeam)
            <p class="text-xs text-gray-500">Pilih pemain per pertandingan. Roster tiap match bisa berbeda. Administrator bisa mengubah kapan saja. Nama peserta diisi administrator.</p>
        @else
            <p class="text-xs text-gray-500">Pilih pemain per pertandingan. Roster tiap match bisa berbeda. Input dibuka sampai sehari sebelum tanggal pertandingan pukul 23:59:59. Nama peserta diisi administrator.</p>
        @endif
        @if ($game)
            <p class="text-xs text-brand-cyan mt-2">Ketentuan {{ $game->name }}: minimum {{ $rosterMin }} · maximum {{ $rosterMax }} pemain. Jumlah dipilih harus di dalam rentang itu.</p>
        @endif
    </div>

    @if ($games->isEmpty())
        <div class="bg-brand-card border border-white/5 rounded-2xl p-6 text-sm text-gray-500">
            Belum ada jadwal pertandingan yang memakai roster.
            @if ($canPickTeam)
                Buat jadwal di menu Jadwal untuk game dengan roster on.
            @else
                Tunggu panitia menjadwalkan pertandingan grup Anda.
            @endif
        </div>
    @else
        <form method="GET" class="grid {{ $canPickTeam ? 'md:grid-cols-3' : 'md:grid-cols-2' }} gap-4 max-w-4xl">
            <div>
                <label class="text-xs text-gray-500">Game</label>
                <select name="game_id" onchange="const m=this.form.elements.namedItem('match_id'); if(m){ m.value=''; } const t=this.form.elements.namedItem('team_id'); if(t){ t.value=''; } this.form.submit()">
                    @foreach ($games as $item)
                        <option value="{{ $item->id }}" @selected($item->id === $game->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Jadwal pertandingan</label>
                <select name="match_id" onchange="const t=this.form.elements.namedItem('team_id'); if(t){ t.value=''; } this.form.submit()">
                    <option value="" disabled>Pilih jadwal</option>
                    @foreach ($matches as $item)
                        <option value="{{ $item->id }}" @selected($item->id === $match->id)>{{ $item->scheduleLabel() }}</option>
                    @endforeach
                </select>
            </div>
            @if ($canPickTeam)
                <div>
                    <label class="text-xs text-gray-500">Grup</label>
                    <select name="team_id" onchange="this.form.submit()">
                        @foreach ($teams as $item)
                            <option value="{{ $item->id }}" @selected($item->id === $team->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>

        @if ($deadlineCountdown)
            <div id="roster-deadline" class="bg-brand-card border border-white/5 rounded-2xl p-5 max-w-4xl" data-remaining="{{ $deadlineCountdown['remaining'] }}" data-expired="{{ $deadlineCountdown['expired'] ? '1' : '0' }}">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 mb-3">{{ $deadlineCountdown['expired'] ? 'Batas input roster sudah habis' : 'Countdown batas input roster' }}</p>
                <div class="flex flex-wrap gap-3 mb-2">
                    <div class="bg-black/30 border border-white/10 rounded-xl px-4 py-3 min-w-[64px] text-center"><p id="roster-cd-days" class="font-gaming text-2xl text-brand-cyan">{{ $deadlineCountdown['days'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Hari</p></div>
                    <div class="bg-black/30 border border-white/10 rounded-xl px-4 py-3 min-w-[64px] text-center"><p id="roster-cd-hours" class="font-gaming text-2xl text-brand-cyan">{{ $deadlineCountdown['hours'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Jam</p></div>
                    <div class="bg-black/30 border border-white/10 rounded-xl px-4 py-3 min-w-[64px] text-center"><p id="roster-cd-minutes" class="font-gaming text-2xl text-brand-cyan">{{ $deadlineCountdown['minutes'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Menit</p></div>
                    <div class="bg-black/30 border border-white/10 rounded-xl px-4 py-3 min-w-[64px] text-center"><p id="roster-cd-seconds" class="font-gaming text-2xl text-brand-cyan">{{ $deadlineCountdown['seconds'] }}</p><p class="text-[10px] uppercase text-gray-500 mt-1">Detik</p></div>
                </div>
                <p class="text-xs text-gray-500">{{ $deadlineCountdown['label'] }}</p>
            </div>
        @endif

        @if ($locked)
            <p class="text-sm text-brand-gold">
                @if ($lockReason === 'deadline')
                    Roster terkunci. Batas input adalah sehari sebelum pertandingan pukul 23:59:59.
                @else
                    Roster terkunci. Pertandingan ini sudah selesai.
                @endif
            </p>
        @endif

        <form
            method="POST"
            action="{{ $formAction }}"
            id="roster-form"
            class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4"
            data-min="{{ $rosterMin }}"
            data-max="{{ $rosterMax }}"
            data-locked="{{ $locked ? '1' : '0' }}"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="match_id" value="{{ $match->id }}">
            <input type="hidden" name="team_id" value="{{ $team->id }}">

            <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                <p id="roster-count" class="text-gray-400">Dipilih <span class="font-gaming text-brand-cyan">0</span> / {{ $rosterMax }} (min {{ $rosterMin }})</p>
                <p id="roster-hint" class="text-xs text-gray-500"></p>
            </div>

            @if ($participants->isNotEmpty())
                <div>
                    <label for="roster-search" class="text-xs text-gray-500">Cari nama peserta</label>
                    <input
                        id="roster-search"
                        type="search"
                        autocomplete="off"
                        placeholder="Ketik nama peserta..."
                        class="mt-1 w-full max-w-md"
                    >
                    <p id="roster-search-empty" class="hidden mt-2 text-sm text-gray-500">Tidak ada peserta yang cocok.</p>
                </div>
            @endif

            @error('participant_ids')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror
            @error('match_id')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror

            <div id="roster-list" class="space-y-2">
                @forelse ($participants as $participant)
                    @php $selected = in_array($participant->id, $selectedIds, true); @endphp
                    <label
                        class="roster-row group flex items-center gap-4 rounded-xl border px-4 py-3.5 cursor-pointer select-none transition {{ $locked ? 'opacity-60 cursor-not-allowed' : 'hover:border-brand-cyan/40' }} {{ $selected ? 'is-selected border-brand-cyan/50 bg-brand-cyan/10' : 'border-white/10 bg-black/20' }}"
                        data-name="{{ Str::lower($participant->name) }}"
                    >
                        <span class="roster-box relative flex h-5 w-5 shrink-0 items-center justify-center rounded-md border {{ $selected ? 'border-brand-cyan bg-brand-cyan text-brand-dark' : 'border-white/30 bg-transparent text-transparent' }}">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                        </span>
                        <input
                            type="checkbox"
                            name="participant_ids[]"
                            value="{{ $participant->id }}"
                            class="roster-check sr-only"
                            @checked($selected)
                            @disabled($locked)
                        >
                        <span class="flex-1 min-w-0">
                            <span class="block font-medium">{{ $participant->name }}</span>
                            @if ($participant->leadershipLabel())
                                <span class="text-[11px] text-gray-500">{{ $participant->leadershipLabel() }}</span>
                            @endif
                        </span>
                        <span class="roster-status text-[10px] uppercase tracking-wider {{ $selected ? 'text-brand-cyan' : 'text-gray-600' }}">{{ $selected ? 'Dipilih' : 'Ketuk untuk pilih' }}</span>
                    </label>
                @empty
                    <p class="text-sm text-gray-500">Belum ada peserta di grup ini. Administrator mengisi nama di menu Peserta & Captain, baru roster bisa dipilih.</p>
                @endforelse
            </div>

            @if ($participants->isNotEmpty() && ! $locked)
                <button id="roster-submit" type="submit" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed">Simpan roster</button>
            @endif
        </form>
    @endif
</section>
@endsection

@section('scripts')
<script>
(function () {
    const box = document.getElementById('roster-deadline');
    if (!box || box.dataset.expired === '1') return;
    const remaining = Number(box.dataset.remaining);
    if (!Number.isFinite(remaining) || remaining <= 0) return;

    const daysEl = document.getElementById('roster-cd-days');
    const hoursEl = document.getElementById('roster-cd-hours');
    const minutesEl = document.getElementById('roster-cd-minutes');
    const secondsEl = document.getElementById('roster-cd-seconds');
    const endsAt = Date.now() + (remaining * 1000);

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function tick() {
        const remain = Math.max(0, endsAt - Date.now());
        const totalSeconds = Math.floor(remain / 1000);
        daysEl.textContent = pad(Math.floor(totalSeconds / 86400));
        hoursEl.textContent = pad(Math.floor((totalSeconds % 86400) / 3600));
        minutesEl.textContent = pad(Math.floor((totalSeconds % 3600) / 60));
        secondsEl.textContent = pad(totalSeconds % 60);
        if (remain <= 0) {
            window.location.reload();
        }
    }

    tick();
    setInterval(tick, 250);
})();

(function () {
    const search = document.getElementById('roster-search');
    const empty = document.getElementById('roster-search-empty');
    const rows = Array.from(document.querySelectorAll('#roster-list .roster-row'));
    if (!search || rows.length === 0) return;

    function filterRows() {
        const query = search.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach((row) => {
            const match = !query || (row.dataset.name || '').includes(query);
            row.classList.toggle('hidden', !match);
            if (match) visible += 1;
        });
        if (empty) empty.classList.toggle('hidden', visible > 0);
    }

    search.addEventListener('input', filterRows);
})();

(function () {
    const form = document.getElementById('roster-form');
    if (!form || form.dataset.locked === '1') return;

    const min = Number(form.dataset.min || 0);
    const max = Number(form.dataset.max || 50);
    const checks = Array.from(form.querySelectorAll('.roster-check'));
    const countEl = document.querySelector('#roster-count span');
    const hintEl = document.getElementById('roster-hint');
    const submit = document.getElementById('roster-submit');

    function selectedCount() {
        return checks.filter((box) => box.checked).length;
    }

    function paintRow(box) {
        const row = box.closest('.roster-row');
        if (!row) return;
        const mark = row.querySelector('.roster-box');
        const statusEl = row.querySelector('.roster-status');
        if (box.checked) {
            row.classList.add('is-selected', 'border-brand-cyan/50', 'bg-brand-cyan/10');
            row.classList.remove('border-white/10', 'bg-black/20');
            if (mark) {
                mark.classList.add('border-brand-cyan', 'bg-brand-cyan', 'text-brand-dark');
                mark.classList.remove('border-white/30', 'bg-transparent', 'text-transparent');
            }
            if (statusEl) {
                statusEl.textContent = 'Dipilih';
                statusEl.classList.add('text-brand-cyan');
                statusEl.classList.remove('text-gray-600');
            }
        } else {
            row.classList.remove('is-selected', 'border-brand-cyan/50', 'bg-brand-cyan/10');
            row.classList.add('border-white/10', 'bg-black/20');
            if (mark) {
                mark.classList.remove('border-brand-cyan', 'bg-brand-cyan', 'text-brand-dark');
                mark.classList.add('border-white/30', 'bg-transparent', 'text-transparent');
            }
            if (statusEl) {
                statusEl.textContent = 'Ketuk untuk pilih';
                statusEl.classList.remove('text-brand-cyan');
                statusEl.classList.add('text-gray-600');
            }
        }
    }

    function sync() {
        const count = selectedCount();
        if (countEl) countEl.textContent = String(count);

        checks.forEach((box) => {
            const row = box.closest('.roster-row');
            const atMax = !box.checked && count >= max;
            box.disabled = atMax;
            if (row) {
                row.classList.toggle('opacity-50', atMax);
                row.classList.toggle('cursor-not-allowed', atMax);
                row.classList.toggle('cursor-pointer', !atMax);
            }
            paintRow(box);
        });

        let hint = '';
        let ok = true;
        if (count < min) {
            hint = 'Kurang ' + (min - count) + ' pemain lagi (minimum ' + min + ').';
            ok = false;
        } else if (count > max) {
            hint = 'Melebihi maksimum ' + max + ' pemain.';
            ok = false;
        } else {
            hint = 'Jumlah roster sudah sesuai ketentuan.';
        }

        if (hintEl) {
            hintEl.textContent = hint;
            hintEl.className = 'text-xs ' + (ok ? 'text-brand-cyan' : 'text-red-400');
        }

        if (submit) submit.disabled = !ok;
    }

    checks.forEach((box) => box.addEventListener('change', sync));
    form.addEventListener('submit', function (event) {
        const count = selectedCount();
        if (count < min || count > max) {
            event.preventDefault();
            sync();
        }
    });
    sync();
})();
</script>
@endsection
