<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class SsoRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $baseUrl = rtrim(
            (string) config('services.sso.base_url'),
            '/'
        );

        $clientId = (string) config('services.sso.client_id');
        $callbackUrl = (string) config('services.sso.callback_url');

        if ($baseUrl === '' || $clientId === '' || $callbackUrl === '') {
            throw new RuntimeException(
                'Konfigurasi SSO SIPO belum lengkap.'
            );
        }

        $state = Str::random(40);

        $request->session()->put('sso_state', $state);

        $authorizeUrl = $baseUrl . '/sso/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'state' => $state,
        ]);

        return redirect()->away($authorizeUrl);
    }
}
