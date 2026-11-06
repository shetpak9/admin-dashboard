<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $adminUsername = config('services.admin.username');
        $adminPasswordHash = config('services.admin.password_hash');

        if (! is_string($adminUsername)
            || ! is_string($adminPasswordHash)
            || $adminUsername === ''
            || $adminPasswordHash === ''
            || ! hash_equals($adminUsername, $validated['username'])
            || ! Hash::check($validated['password'], $adminPasswordHash)) {
            return back()->withErrors([
                'username' => 'The username or password is incorrect.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'admin_authenticated' => true,
            'admin_username' => $validated['username'],
        ]);

        return to_route('index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
