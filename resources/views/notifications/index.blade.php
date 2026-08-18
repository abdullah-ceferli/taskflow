@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('content')
<div class="mx-auto max-w-3xl space-y-3">@forelse($notifications as $notification)<article class="rounded-2xl border bg-white p-5 {{ $notification->read_at ? 'opacity-70' : '' }}"><div class="flex items-center justify-between gap-4"><div><p class="font-semibold">{{ str_replace(['.', '_'], ' ', ucfirst($notification->data['event'] ?? 'Notification')) }}</p><p class="mt-1 text-sm text-slate-500">{{ $notification->created_at->diffForHumans() }}</p></div>@unless($notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="rounded-xl border px-3 py-2 text-sm font-semibold">Mark read</button></form>@endunless</div></article>@empty<p class="rounded-2xl bg-white p-6 text-sm text-slate-500">No notifications.</p>@endforelse<div>{{ $notifications->links() }}</div></div>
@endsection
