@props(['setting' => null, 'active' => 'home', 'title' => null])
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($setting->name ?? 'BedeBest') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif'],gaming:['Orbitron','sans-serif']},colors:{'brand-dark':'#0a0a0f','brand-card':'#12121c','brand-purple':'#8b5cf6','brand-cyan':'#06b6d4','brand-magenta':'#d946ef','brand-gold':'#e4c76b','brand-gold-dim':'#b8963e'}}}}</script>
    <style>
        body{background:#0a0a0f;color:#e4c76b;overflow-x:hidden}
        .neon-text{text-shadow:0 0 10px rgba(139,92,246,.5),0 0 20px rgba(139,92,246,.3)}
        .live-dot{box-shadow:0 0 10px #ef4444}
        .match-card:hover{box-shadow:0 0 25px rgba(139,92,246,.4);transform:translateY(-2px);transition:.3s}
        .clip-polygon{clip-path:polygon(15% 0,100% 0,85% 100%,0 100%)}
        .clip-polygon-reverse{clip-path:polygon(0 0,85% 0,100% 100%,15% 100%)}
        .bg-shape{position:absolute;bottom:0;height:280px;width:220px;opacity:.75;mix-blend-mode:screen}
        .roster-banner{background:radial-gradient(ellipse at center,rgba(228,199,107,.16),transparent 68%)}
        .roster-row{display:flex;gap:10px}
        .roster-card{flex:1;min-width:0;display:flex;flex-direction:column;background:#07070c;border:1px solid rgba(228,199,107,.8);box-shadow:0 0 28px rgba(228,199,107,.12)}
        .roster-mark{text-align:center;font-family:Orbitron,sans-serif;font-size:12px;letter-spacing:.28em;color:#e4c76b;padding:10px 0 8px}
        .roster-photo{height:340px;width:100%;object-fit:cover;object-position:center 18%;display:block;filter:contrast(1.05) saturate(1.08)}
        .roster-plate{margin:-22px 14px 14px;position:relative;z-index:2;background:linear-gradient(#fff,#e6e1d4);color:#111;text-align:center;font-family:Orbitron,sans-serif;font-weight:700;font-size:13px;letter-spacing:.12em;padding:9px 6px;clip-path:polygon(7% 0,93% 0,100% 100%,0 100%)}
        @media(max-width:768px){
            .roster-row{overflow-x:auto;padding-bottom:6px}
            .roster-card{min-width:168px}
            .roster-photo{height:220px}
            .roster-plate{font-size:11px}
        }
    </style>
</head>
<body class="font-sans antialiased text-brand-gold">
<nav class="fixed w-full z-50 backdrop-blur-md bg-brand-dark/80 border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            @if ($setting?->logoUrl())
                <img src="{{ $setting->logoUrl() }}" alt="{{ $setting->name }}" class="w-10 h-10 rounded-full object-cover">
            @else
                <span class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-purple to-brand-cyan flex items-center justify-center font-gaming font-black text-sm">BB</span>
            @endif
            <span class="font-gaming text-xl font-bold">{{ strtoupper($setting->name ?? 'BEDEBEST') }}<span class="text-brand-purple">.</span></span>
        </a>
        <div class="hidden md:flex items-center space-x-5 text-sm font-medium uppercase tracking-wider">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-brand-gold border-b-2 border-brand-purple py-2' : 'text-gray-300 hover:text-brand-cyan' }}">Home</a>
            <a href="{{ route('jadwal') }}" class="{{ $active === 'jadwal' ? 'text-brand-gold border-b-2 border-brand-purple py-2' : 'text-gray-300 hover:text-brand-cyan' }}">Jadwal</a>
            <a href="{{ route('games') }}" class="{{ $active === 'games' ? 'text-brand-gold border-b-2 border-brand-purple py-2' : 'text-gray-300 hover:text-brand-cyan' }}">Games</a>
            <a href="{{ route('tim') }}" class="{{ $active === 'tim' ? 'text-brand-gold border-b-2 border-brand-purple py-2' : 'text-gray-300 hover:text-brand-cyan' }}">Tim</a>
            <a href="{{ route('bagan') }}" class="{{ $active === 'bagan' ? 'text-brand-gold border-b-2 border-brand-purple py-2' : 'text-gray-300 hover:text-brand-cyan' }}">Bagan</a>
            <a href="{{ route('leaderboard') }}" class="{{ $active === 'leaderboard' ? 'text-brand-gold border-b-2 border-brand-purple py-2' : 'text-gray-300 hover:text-brand-cyan' }}">Leaderboard</a>
        </div>
        @auth
            @if (auth()->user()->isAdministrator())
                <a href="{{ route('panitia.dashboard') }}" class="hidden md:inline-block bg-gradient-to-r from-brand-purple to-brand-cyan px-6 py-2.5 rounded-full font-semibold text-sm uppercase">Panel Panitia</a>
            @else
                <a href="{{ route('pic.jadwal') }}" class="hidden md:inline-block bg-gradient-to-r from-brand-purple to-brand-cyan px-6 py-2.5 rounded-full font-semibold text-sm uppercase">Panel Tim</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="hidden md:inline-block bg-gradient-to-r from-brand-purple to-brand-cyan px-6 py-2.5 rounded-full font-semibold text-sm uppercase">Login PIC</a>
        @endauth
    </div>
</nav>
{{ $slot }}
<footer class="border-t border-white/10 py-8 text-center text-xs text-gray-600">&copy; {{ now()->year }} {{ $setting->name ?? 'BedeBest' }} Tournament</footer>
</body>
</html>
