<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Panitia') | BedeBest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['-apple-system','BlinkMacSystemFont','Helvetica Neue','Helvetica','Arial','sans-serif'],gaming:['-apple-system','BlinkMacSystemFont','Helvetica Neue','Helvetica','Arial','sans-serif']},colors:{'brand-dark':'#0a0a0f','brand-card':'#12121c','brand-purple':'#8b5cf6','brand-cyan':'#06b6d4','brand-magenta':'#d946ef','brand-gold':'#e4c76b'}}}}</script>
    <style>
        body{background:#0a0a0f;color:#e4c76b}
        input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=hidden]),
        select,
        textarea{
            background:#0a0a0f;
            border:1px solid rgba(255,255,255,.16);
            border-radius:1rem;
            padding:.9rem 1.1rem;
            width:100%;
            min-height:3.1rem;
            color:#e4c76b;
            font-size:.95rem;
            line-height:1.35;
            transition:border-color .15s ease, box-shadow .15s ease;
        }
        textarea{min-height:7rem;resize:vertical}
        select{
            -webkit-appearance:none;
            appearance:none;
            cursor:pointer;
            padding-right:2.75rem;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23e4c76b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat;
            background-position:right 1rem center;
            background-size:1.1rem;
        }
        input:not([type=checkbox]):not([type=radio]):not([type=file]):focus,
        select:focus,
        textarea:focus{
            outline:none;
            border-color:rgba(6,182,212,.55);
            box-shadow:0 0 0 3px rgba(6,182,212,.16);
        }
        input::placeholder,textarea::placeholder{color:#6b7280}
        input.input-error,select.input-error,textarea.input-error{border-color:#f87171}
        input[type=checkbox],input[type=radio]{width:1.1rem;height:1.1rem;min-height:0;padding:0;border-radius:.35rem;accent-color:#06b6d4}
        @media(max-width:767px){
            #app-sidebar{position:fixed;inset:0 auto 0 0;width:16rem;transform:translateX(-100%);transition:transform .2s ease;z-index:50}
            #app-sidebar.is-open{transform:translateX(0)}
        }
    </style>
    @yield('head')
</head>
<body class="font-sans text-brand-gold">
<div class="min-h-screen md:grid md:grid-cols-[240px_1fr]">
    <header class="md:hidden sticky top-0 z-40 flex items-center justify-between gap-3 px-4 h-16 bg-black/80 border-b border-white/10 backdrop-blur">
        <p class="font-gaming text-lg">BEDEBEST<span class="text-brand-purple">.</span></p>
        <button id="sidebar-toggle" type="button" class="inline-flex items-center justify-center w-11 h-11 rounded-xl border border-white/15" aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </header>
    <div id="sidebar-backdrop" class="hidden md:hidden fixed inset-0 z-40 bg-black/60"></div>
    <aside id="app-sidebar" class="bg-[#0a0a0f] md:bg-black/40 border-r border-white/10 p-6">
        <p class="font-gaming text-lg mb-8 hidden md:block">BEDEBEST<span class="text-brand-purple">.</span></p>
        <p class="text-[10px] uppercase text-brand-purple tracking-widest mb-1">Administrator</p>
        <p class="text-xs text-gray-400 mb-3 truncate" title="{{ auth()->user()?->name }}">{{ auth()->user()?->name }}</p>
        <nav class="space-y-2 text-sm">
            <a href="{{ route('panitia.dashboard') }}" class="block {{ request()->routeIs('panitia.dashboard') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="block {{ request()->routeIs('profile.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Akun saya</a>
            <a href="{{ route('panitia.users.index') }}" class="block {{ request()->routeIs('panitia.users.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">User</a>
            <a href="{{ route('panitia.grup.index') }}" class="block {{ request()->routeIs('panitia.grup.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Master grup</a>
            <a href="{{ route('panitia.banners.edit') }}" class="block {{ request()->routeIs('panitia.banners.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Banner beranda</a>
            <a href="{{ route('panitia.gallery.index') }}" class="block {{ request()->routeIs('panitia.gallery.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Gallery</a>
            <a href="{{ route('panitia.games.index') }}" class="block {{ request()->routeIs('panitia.games.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Master games</a>
            <a href="{{ route('panitia.matches.index') }}" class="block {{ request()->routeIs('panitia.matches.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Jadwal</a>
            <a href="{{ route('panitia.participants.index') }}" class="block {{ request()->routeIs('panitia.participants.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Peserta & Captain</a>
            <a href="{{ route('panitia.rosters.edit') }}" class="block {{ request()->routeIs('panitia.rosters.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Pilih roster</a>
            <a href="{{ route('panitia.scores.index') }}" class="block {{ request()->routeIs('panitia.scores.*') ? 'text-brand-gold' : 'text-gray-400 hover:text-brand-gold' }}">Skor & poin</a>
            <a href="{{ route('home') }}" class="block text-brand-cyan pt-4">Lihat situs publik</a>
            <form method="POST" action="{{ route('logout') }}" class="pt-2">
                @csrf
                <button class="text-gray-500 hover:text-brand-gold">Keluar</button>
            </form>
        </nav>
    </aside>
    <main class="p-4 md:p-10 space-y-8 min-w-0">
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
@yield('scripts')
@include('partials.confirm-dialog')
<script>
(function () {
    const button = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!button || !sidebar || !backdrop) return;

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        backdrop.classList.toggle('hidden', !open);
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
        button.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    }

    button.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('is-open'));
    });
    backdrop.addEventListener('click', function () { setOpen(false); });
    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () { setOpen(false); });
    });
})();
</script>
</body>
</html>
