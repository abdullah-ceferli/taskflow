@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $classes = match ($value) {
        'active', 'done', 'completed' => 'bg-emerald-100 text-emerald-700',
        'in_progress', 'review' => 'bg-sky-100 text-sky-700',
        'archived', 'cancelled' => 'bg-amber-100 text-amber-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>{{ str($value)->replace('_', ' ')->title() }}</span>