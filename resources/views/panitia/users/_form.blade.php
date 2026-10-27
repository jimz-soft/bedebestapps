@php
    $role = old('role', $user?->role ?? 'user');
@endphp
<div>
    <label class="text-xs text-gray-500">Nama</label>
    <input name="name" value="{{ old('name', $user?->name ?? '') }}" required>
</div>
<div>
    <label class="text-xs text-gray-500">Username</label>
    <input name="username" value="{{ old('username', $user?->username ?? '') }}" required>
</div>
<div>
    <label class="text-xs text-gray-500">Password {{ $user ? '(kosongkan jika tidak diubah)' : '' }}</label>
    <input type="password" name="password" {{ $user ? '' : 'required' }}>
</div>
<div>
    <label class="text-xs text-gray-500">Role</label>
    <select name="role">
        <option value="administrator" @selected($role === 'administrator')>Administrator</option>
        <option value="user" @selected($role === 'user')>User</option>
    </select>
    <p class="text-[11px] text-gray-500 mt-2">Administrator = panitia, bisa master game, jadwal, peserta, skor, dan poin. User = akun biasa / PIC tim.</p>
</div>
<div>
    <label class="text-xs text-gray-500">Tim (wajib untuk role user)</label>
    <select name="team_id">
        <option value="">—</option>
        @foreach ($teams as $team)
            <option value="{{ $team->id }}" @selected((string) old('team_id', $user?->team_id ?? '') === (string) $team->id)>{{ $team->name }}</option>
        @endforeach
    </select>
</div>
<label class="text-xs text-gray-400 flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" class="w-4" @checked(old('is_active', $user?->is_active ?? true))>
    Akun aktif
</label>
