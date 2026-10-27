@extends('layouts.panitia')

@section('title', 'User baru')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6 max-w-2xl">
    <h1 class="font-gaming text-2xl mb-6">USER BARU</h1>
    <form method="POST" action="{{ route('panitia.users.store') }}" class="space-y-4">
        @csrf
        @include('panitia.users._form', ['user' => null])
        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan user</button>
    </form>
</section>
@endsection
