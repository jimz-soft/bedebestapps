@extends($layout)

@section('title', 'Akun saya')

@section('content')
<section class="space-y-6 max-w-2xl">
    <div>
        <h1 class="font-gaming text-2xl mb-2">AKUN SAYA</h1>
        <p class="text-xs text-gray-500">Lihat profil login Anda dan ubah password secara mandiri.</p>
    </div>

    <div class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4">
        <h2 class="font-gaming text-lg">Profil</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-500 mb-1">Nama</p>
                <p class="text-brand-gold">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Username</p>
                <p class="text-brand-gold">{{ $user->username }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Peran</p>
                <p class="text-brand-gold">{{ $user->roleLabel() }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Grup</p>
                <p class="text-brand-gold">{{ $user->team?->name ?? '—' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.password') }}" class="bg-brand-card border border-white/5 rounded-2xl p-6 space-y-4" data-confirm-title="Ubah password" data-confirm="Password akun ini akan diganti. Pastikan password baru sudah diingat. Lanjutkan?" data-confirm-action="Ya, ubah password">
        @csrf
        @method('PUT')
        <h2 class="font-gaming text-lg">Ubah password</h2>
        <div>
            <label class="text-xs text-gray-500">Password saat ini</label>
            <input type="password" name="current_password" required autocomplete="current-password" class="{{ $errors->has('current_password') ? 'input-error' : '' }}">
            @error('current_password')
                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="text-xs text-gray-500">Password baru</label>
            <input type="password" name="password" required minlength="6" autocomplete="new-password" class="{{ $errors->has('password') ? 'input-error' : '' }}">
            @error('password')
                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="text-xs text-gray-500">Ulangi password baru</label>
            <input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password">
        </div>
        <button type="submit" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan password</button>
    </form>
</section>
@endsection
