@extends('layouts.panitia')

@section('title', 'User')

@section('content')
<section class="bg-brand-card border border-white/5 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="font-gaming text-2xl">USER</h1>
        <a href="{{ route('panitia.users.create') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-brand-purple to-brand-cyan text-sm font-semibold">User baru</a>
    </div>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-gray-500">
            <tr><th class="text-left py-2">Nama</th><th>Username</th><th>Role</th><th>Tim</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr class="border-t border-white/5">
                    <td class="py-3">{{ $user->name }}</td>
                    <td class="text-center">{{ $user->username }}</td>
                    <td class="text-center">{{ $user->roleLabel() }}</td>
                    <td class="text-center">{{ $user->team?->name ?? '—' }}</td>
                    <td class="text-center">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="text-right"><a href="{{ route('panitia.users.edit', $user) }}" class="text-brand-cyan">Ubah</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
