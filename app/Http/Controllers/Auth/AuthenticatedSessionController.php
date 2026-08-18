<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function registerStore(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->trim()->toString(),
            'email' => $request->string('email')->lower()->trim()->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);
        $user->assignRole(UserRole::Member->value);

        Auth::login($user);
        $request->session()->regenerate();

        activity('auth')->causedBy($user)->event('auth.registered')->log('auth.registered');

        return redirect()->route('dashboard.index');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        activity('auth')->causedBy($request->user())->event('auth.login')->log('auth.login');

        return redirect()->intended(route('dashboard.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        activity('auth')->causedBy($request->user())->event('auth.logout')->log('auth.logout');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
