@extends('layouts.app')
@section('title', 'Tasks')
@section('page-title', 'Tasks')
@section('content')
<section class="rounded-3xl bg-slate-950 p-7 text-white"><p class="text-sm font-semibold uppercase tracking-widest text-indigo-300">Task workspace</p><h2 class="mt-3 text-3xl font-semibold">Make project work visible and actionable.</h2></section>
<livewire:tasks.task-filters />
@endsection