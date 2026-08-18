@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <main class="grid min-h-screen place-items-center bg-[radial-gradient(circle_at_15%_20%,rgba(99,102,241,.25),transparent_32%),linear-gradient(135deg,#020617,#172554)] px-5 py-10">
        <section class="w-full max-w-md rounded-3xl border border-white/15 bg-white p-7 shadow-2xl shadow-slate-950/40 sm:p-9">
            <div class="flex items-center gap-3 text-lg font-bold tracking-tight text-slate-950">
                <span class="grid size-10 place-items-center rounded-xl bg-indigo-600 text-sm font-black text-white">T</span>
                TaskFlow
            </div>
            <div class="mt-8">
                <p class="text-sm font-semibold uppercase tracking-[.18em] text-indigo-600">Workspace access</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Welcome back</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to continue to your TaskFlow workspace.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 @error('email') border-rose-400 @enderror">
                    <x-form-error name="email" />
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 @error('password') border-rose-400 @enderror">
                    <x-form-error name="password" />
                </div>
                <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-600"><input name="remember" type="checkbox" value="1" @checked(old('remember')) class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">Remember me</label>
                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/25">Sign in to TaskFlow</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">No account yet? <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Create a member account</a></p>
        </section>
    </main>
@endsection