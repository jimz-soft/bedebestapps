@extends('layouts.panitia')

@section('title', 'Dashboard')

@section('content')
<section>
    <h1 class="font-gaming text-2xl mb-6 text-brand-gold">DASHBOARD</h1>
    <div class="grid sm:grid-cols-4 gap-4">
        <div class="bg-brand-card rounded-xl p-4"><p class="text-xs text-gray-500">Grup</p><p class="font-gaming text-3xl">{{ $teamCount }}</p></div>
        <div class="bg-brand-card rounded-xl p-4"><p class="text-xs text-gray-500">Games</p><p class="font-gaming text-3xl">{{ $gameCount }}</p></div>
        <div class="bg-brand-card rounded-xl p-4"><p class="text-xs text-gray-500">Match live</p><p class="font-gaming text-3xl text-red-400">{{ $liveCount }}</p></div>
        <div class="bg-brand-card rounded-xl p-4"><p class="text-xs text-gray-500">Match selesai</p><p class="font-gaming text-3xl">{{ $finishedCount }}</p></div>
    </div>
    <div class="flex flex-wrap gap-3 mt-8">
        <a href="{{ route('panitia.users.index') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Maintenance user</a>
        <a href="{{ route('panitia.grup.index') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Master grup</a>
        <a href="{{ route('panitia.games.index') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Master games</a>
        <a href="{{ route('panitia.matches.index') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Jadwal</a>
        <a href="{{ route('panitia.participants.index') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Peserta & kapten</a>
        <a href="{{ route('panitia.rosters.edit') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Pilih roster</a>
        <a href="{{ route('panitia.scores.index') }}" class="px-5 py-2 rounded-full bg-white/10 text-sm">Skor & poin</a>
        <a href="{{ route('panitia.banners.edit') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Banner halaman utama</a>
    </div>
</section>
@endsection
