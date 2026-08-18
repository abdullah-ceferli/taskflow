@extends('layouts.app')

@section('title', 'User administration')
@section('page-title', 'Users and roles')

@section('content')
<div class="mb-5 flex justify-end"><a href="{{ route('admin.operations.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-700">Operations dashboard</a></div>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-5 py-3 text-left">User</th><th class="px-5 py-3 text-left">Workspaces</th><th class="px-5 py-3 text-left">Global role</th></tr></thead><tbody class="divide-y divide-slate-100">
    @foreach($users as $managedUser)<tr><td class="px-5 py-4"><p class="font-semibold">{{ $managedUser->name }}</p><p class="text-slate-500">{{ $managedUser->email }}</p></td><td class="px-5 py-4 text-slate-600">{{ $managedUser->workspaces->pluck('name')->join(', ') ?: 'None' }}</td><td class="px-5 py-4"><form class="flex gap-2" method="POST" action="{{ route('admin.users.update', $managedUser) }}">@csrf @method('PATCH')<select name="role" class="rounded-xl border-slate-300">@foreach($roles as $role)<option value="{{ $role->value }}" @selected($managedUser->hasRole($role->value))>{{ ucfirst(str_replace('_', ' ', $role->value)) }}</option>@endforeach</select><button class="rounded-xl bg-indigo-600 px-3 py-2 font-semibold text-white">Save</button></form></td></tr>@endforeach
    </tbody></table></div><div class="p-5">{{ $users->links() }}</div>
</div>
@endsection
