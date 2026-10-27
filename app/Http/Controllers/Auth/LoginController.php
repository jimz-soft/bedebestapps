<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'setting' => Setting::query()->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'Username atau password tidak cocok.',
            ])->onlyInput('username');
        }

        $user = $request->user();

        if ($user === null || $user->is_active !== true) {
            Auth::logout();

            return back()->withErrors([
                'username' => 'Akun ini nonaktif. Hubungi administrator.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        if ($user->isAdministrator()) {
            return redirect()->intended(route('panitia.dashboard'));
        }

        return redirect()->intended(route('pic.jadwal'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
