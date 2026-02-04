<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        // tambah login pakai NIP


        $request->validate([
            'credential' => 'required',
            'password' => 'required',
        ]);

        // Cek apakah email terdaftar
        $user = User::where('email', $request->credential)->orWhere('nip', $request->credential)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Akun tidak terdaftar.',
            ])->onlyInput('email');
        }

        $field = $user->email === $request->credential ? 'email' : 'nip';

        // Coba autentikasi
        if (!Auth::attempt([$field => $request->credential, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors([
                'password' => 'Password salah.',
            ])->onlyInput('email');
        }

        // Regenerate session
        $request->session()->regenerate();

        // Redirect sesuai role
        return redirect()->route(Auth::user()->role->nm_role . '.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
