<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;

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

    public function registerStore(RegisterRequest $request, ActivityRecorder $activity, AuthenticationService $authentication): RedirectResponse
    {
        ['user' => $user, 'workspace' => $workspace] = $authentication->registerMember(
            $request->string('name')->trim()->toString(),
            $request->string('email')->lower()->trim()->toString(),
            $request->string('password')->toString(),
        );

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('current_workspace_id', $workspace->id);

        $activity->record(ActivityEvent::AuthRegistered, $user, $user);

        return redirect()->route('dashboard.index');
    }

    public function store(LoginRequest $request, ActivityRecorder $activity): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $activity->record(ActivityEvent::AuthLogin, $request->user(), $request->user());

        return redirect()->intended(route('dashboard.index'));
    }

    public function destroy(Request $request, ActivityRecorder $activity): RedirectResponse
    {
        $activity->record(ActivityEvent::AuthLogout, $request->user(), $request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
