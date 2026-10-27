<x-layouts.public :setting="$setting" active="login" title="Login | BedeBest">
<main class="min-h-screen flex items-center justify-center p-6 pt-32">
    <div class="w-full max-w-md bg-brand-card border border-white/10 rounded-2xl p-8">
        <div class="text-center mb-8">
            <span class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-purple to-brand-cyan inline-flex items-center justify-center font-gaming font-black">BB</span>
            <h1 class="font-gaming text-2xl mt-4 text-brand-gold">LOGIN</h1>
            <p class="text-xs text-gray-500 mt-2">Administrator (master data, skor) atau user (jadwal, profil tim, roster)</p>
        </div>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label class="text-xs uppercase text-gray-500">Username</label>
            <input name="username" value="{{ old('username') }}" class="w-full mt-1 mb-1 bg-black/40 border border-white/10 rounded-xl px-4 py-3" required>
            @error('username')
                <p class="text-xs text-red-400 mb-3">{{ $message }}</p>
            @enderror
            <label class="text-xs uppercase text-gray-500 mt-3 block">Password</label>
            <input type="password" name="password" class="w-full mt-1 mb-6 bg-black/40 border border-white/10 rounded-xl px-4 py-3" required>
            <button class="w-full bg-gradient-to-r from-brand-purple to-brand-cyan rounded-full py-3 font-semibold uppercase text-sm">Masuk</button>
        </form>
        <p class="text-center mt-6"><a href="{{ route('home') }}" class="text-xs text-brand-cyan">Kembali ke home</a></p>
    </div>
</main>
</x-layouts.public>
