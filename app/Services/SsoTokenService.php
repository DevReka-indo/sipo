<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SsoTokenService
{
    public function verify(string $token): array
    {
        $baseUrl = rtrim((string) config('services.sso.base_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Konfigurasi SSO_BASE_URL belum diatur.');
        }

        $response = Http::asForm()
            ->acceptJson()
            ->post($baseUrl . '/api/sso/verify-token', [
                'client_id' => config('services.sso.client_id'),
                'client_secret' => config('services.sso.client_secret'),
                'sso_token' => $token,
            ]);

        // Debug: aktifkan kalau verifikasi token selalu gagal dan tidak tahu
        // response asli dari server SSO (mis. salah endpoint, client_secret salah,
        // atau format response SSO berbeda dari yang diharapkan).
        // Log::info('SSO verify-token response', [
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);
        // dd($response->status(), $response->json());

        if (! $response->successful()) {
            throw new RuntimeException(
                $response->json('message', 'Token SSO tidak valid.')
            );
        }

        $user = $response->json('user');

        if (! is_array($user)) {
            throw new RuntimeException('Response user dari SSO tidak valid.');
        }

        return $user;
    }
}
