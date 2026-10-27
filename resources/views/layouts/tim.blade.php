<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel tim') | BedeBest</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif'],gaming:['Orbitron','sans-serif']},colors:{'brand-dark':'#0a0a0f','brand-card':'#12121c','brand-purple':'#8b5cf6','brand-cyan':'#06b6d4','brand-magenta':'#d946ef','brand-gold':'#e4c76b'}}}}</script>
    <style>body{background:#0a0a0f;color:#e4c76b}input,select,textarea{background:#0a0a0f;border:1px solid rgba(255,255,255,.1);border-radius:.75rem;padding:.5rem .75rem;width:100%;color:#e4c76b}input::placeholder,textarea::placeholder{color:#6b7280}</style>
</head>
<body class="font-sans text-brand-gold">
<div class="min-h-screen md:grid md:grid-cols-[240px_1fr]">
    <aside class="bg-black/40 border-r border-white/10 p-6">
        <p class="font-gaming text-lg mb-8">BEDEBEST<span class="text-brand-purple">.</span></p>
        <p class="text-[10px] uppercase text-brand-purple tracking-widest mb-3">{{ auth()->user()?->team?->name ?? 'Grup' }}</p>
        <nav class="space-y-2 text-sm">
            <a href="{{ route('pic.jadwal') }}" class="block {{ request()->routeIs('pic.jadwal') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Jadwal</a>
            <a href="{{ route('pic.profil') }}" class="block {{ request()->routeIs('pic.profil') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Profil tim</a>
            <a href="{{ route('pic.rosters.edit') }}" class="block {{ request()->routeIs('pic.rosters.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Pilih roster</a>
            <a href="{{ route('home') }}" class="block text-brand-cyan pt-4">Lihat situs publik</a>
            <form method="POST" action="{{ route('logout') }}" class="pt-2">
                @csrf
                <button class="text-gray-500 hover:text-brand-gold">Keluar</button>
            </form>
        </nav>
    </aside>
    <main class="p-6 md:p-10 space-y-8">
        @if (session('status'))
            <p class="bg-brand-cyan/10 border border-brand-cyan/40 rounded-xl px-4 py-3 text-sm text-brand-cyan">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="bg-red-500/10 border border-red-500/40 rounded-xl px-4 py-3 text-sm text-red-300">{{ session('error') }}</p>
        @endif
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/40 rounded-xl px-4 py-3 text-sm text-red-300">
                <p class="font-semibold mb-1">Simpan ditolak</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
