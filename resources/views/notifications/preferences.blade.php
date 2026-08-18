@extends('layouts.app')
@section('title', 'Notification preferences')
@section('page-title', 'Notification preferences')
@section('content')
<div class="mx-auto max-w-3xl space-y-3">@foreach($events as $event)@php($preference = $preferences->get($event))<form method="POST" action="{{ route('notifications.preferences.update') }}" class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-white p-5">@csrf @method('PUT')<input type="hidden" name="event" value="{{ $event }}"><p class="font-semibold">{{ ucfirst(str_replace(['.', '_'], ' ', $event)) }}</p><div class="flex items-center gap-4 text-sm"><label><input type="checkbox" name="in_app" value="1" @checked($preference?->in_app ?? true)> In app</label><label><input type="checkbox" name="email" value="1" @checked($preference?->email ?? false)> Email</label><button class="rounded-xl bg-indigo-600 px-3 py-2 font-semibold text-white">Save</button></div></form>@endforeach</div>
@endsection
