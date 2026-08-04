<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SsoLoginController extends Controller
{
    public function __invoke(): View
    {
        // Halaman antara sebelum redirect ke SSO pusat.
        // Bisa berisi tombol "Lanjutkan dengan SSO" atau auto-redirect via meta refresh / JS.
        return view('auth.sso-login');
    }
}
