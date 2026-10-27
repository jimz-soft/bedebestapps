@php
    $isEdit = $user->exists;
@endphp
@extends('layouts.panitia')

@section('title', $isEdit ? 'Ubah user' : 'User baru')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6 max-w-2xl">
    <h1 class="font-gaming text-2xl mb-6">{{ $isEdit ? 'UBAH USER' : 'USER BARU' }}</h1>
    <form method="POST" action="{{ $isEdit ? route('panitia.users.update', $user) : route('panitia.users.store') }}" class="space-y-4">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif
        <div>
            <label class="text-xs text-gray-500">Nama</label>
            <input name="name" value="{{ old('name', $user->name) }}" required>
            @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs text-gray-500">Username</label>
            <input name="username" value="{{ old('username', $user->username) }}" required>
            @error('username')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs text-gray-500">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input type="password" name="password" {{ $isEdit ? '' : 'required' }}>
            @error('password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs text-gray-500">Role</label>
            <select name="role">
                <option value="administrator" @selected(old('role', $user->role) === 'administrator')>Administrator (panitia)</option>
                <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
            </select>
            @error('role')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs text-gray-500">Tim (wajib untuk role user)</label>
            <select name="team_id">
                <option value="">—</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) old('team_id', $user->team_id) === (string) $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
            @error('team_id')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="w-4" @checked(old('is_active', $user->exists ? $user->is_active : true))>
            Aktif
        </label>
        <p class="text-[11px] text-gray-500">Nonaktifkan centang Aktif untuk menonaktifkan akun. Administrator mengisi master game, jadwal, peserta, kapten, skor, dan poin.</p>
        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan</button>
    </form>
</section>
@endsection
