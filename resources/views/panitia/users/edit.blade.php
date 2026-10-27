@extends('layouts.panitia')

@section('title', 'Ubah user')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6 max-w-2xl">
    <h1 class="font-gaming text-2xl mb-6">UBAH USER</h1>
    <form method="POST" action="{{ route('panitia.users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('panitia.users._form', ['user' => $user])
        <button class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">Simpan perubahan</button>
    </form>
</section>
@endsection
