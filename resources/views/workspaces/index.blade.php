@extends('layouts.app')

@section('title', 'Workspaces')
@section('page-title', 'Choose workspace')

@section('content')
<div class="mx-auto max-w-3xl space-y-4">
    @forelse($workspaces as $workspace)
        <article class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div><h2 class="font-semibold text-slate-950">{{ $workspace->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ ucfirst($workspace->pivot->role) }}</p></div>
            @if($current?->id === $workspace->id)
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Current</span>
            @else
                <form method="POST" action="{{ route('workspaces.switch', $workspace) }}">@csrf<button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Switch</button></form>
            @endif
        </article>
    @empty
        <p class="rounded-2xl bg-white p-6 text-sm text-slate-500">No workspace membership is available.</p>
    @endforelse
</div>
@endsection
