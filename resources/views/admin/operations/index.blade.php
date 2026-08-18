@extends('layouts.app')

@section('title', 'Operations')
@section('page-title', 'Operational health')

@section('content')
<div class="grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Readiness</p><h2 class="mt-2 text-xl font-semibold">Service dependencies</h2></div>
            <span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-800' => $health['healthy'], 'bg-rose-100 text-rose-800' => ! $health['healthy']])>{{ $health['healthy'] ? 'Ready' : 'Unavailable' }}</span>
        </div>
        <dl class="mt-6 space-y-3">
            @foreach($health['components'] as $component => $ready)
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"><dt class="font-medium capitalize">{{ $component }}</dt><dd class="text-sm font-semibold {{ $ready ? 'text-emerald-700' : 'text-rose-700' }}">{{ $ready ? 'Ready' : 'Failed' }}</dd></div>
            @endforeach
        </dl>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">SLO signals</p><h2 class="mt-2 text-xl font-semibold">Current operational metrics</h2>
        <dl class="mt-6 grid gap-3 sm:grid-cols-2">
            @foreach($metrics as $metric => $value)
                <div class="rounded-xl bg-slate-50 px-4 py-3"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $metric) }}</dt><dd class="mt-1 text-xl font-semibold text-slate-950">{{ number_format($value, is_float($value) ? 2 : 0) }}</dd></div>
            @endforeach
        </dl>
    </section>
</div>
@endsection
