@extends('layouts.guest')

@section('title', 'Create account')

@section('content')
    <main class="grid min-h-screen place-items-center bg-[radial-gradient(circle_at_15%_20%,rgba(99,102,241,.25),transparent_32%),linear-gradient(135deg,#020617,#172554)] px-5 py-10">
        <section class="w-full max-w-md rounded-3xl border border-white/15 bg-white p-7 shadow-2xl shadow-slate-950/40 sm:p-9">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3 text-lg font-bold tracking-tight text-slate-950"><span class="grid size-10 place-items-center rounded-xl bg-indigo-600 text-sm font-black text-white">T</span>TaskFlow</a>
            <div class="mt-8"><p class="text-sm font-semibold uppercase tracking-[.18em] text-indigo-600">New workspace member</p><h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Create your account</h1><p class="mt-2 text-sm leading-6 text-slate-500">New accounts receive member access. An administrator can grant additional access.</p></div>
            <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
                @csrf
                <div><label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Full name</label><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus class="block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 @error('name') border-rose-400 @enderror"><x-form-error name="name" /></div>
                <div><label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 @error('email') border-rose-400 @enderror"><x-form-error name="email" /></div>
                <div><label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required class="block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 @error('password') border-rose-400 @enderror"><x-form-error name="password" /></div>
                <div><label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15"></div>
                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/25">Create member account</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in</a></p>
        </section>
    </main>
@endsection