<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SsoTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class SsoCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        SsoTokenService $ssoTokenService
    ): RedirectResponse {
        $request->validate([
            'sso_token' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $expectedState = $request->session()->pull('sso_state');
        $receivedState = $request->string('state')->toString();

        if (
            ! $expectedState
            || ! hash_equals($expectedState, $receivedState)
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Login SSO gagal: state tidak valid. Silakan login ulang.'
                );
        }

        try {
            $ssoUser = $ssoTokenService->verify(
                $request->string('sso_token')->toString()
            );

            $employeeId = trim(
                (string) ($ssoUser['employee_id'] ?? '')
            );

            $email = trim(
                (string) ($ssoUser['email'] ?? '')
            );

            /*
            |--------------------------------------------------------------------------
            | Cari user SIPO
            |--------------------------------------------------------------------------
            | 1. employee_id NEXID dicocokkan dengan nip SIPO.
            | 2. Jika tidak ditemukan, cari menggunakan email.
            | 3. Jika tetap tidak ditemukan, buat user baru.
            */

            $user = null;

            if ($employeeId !== '') {
                $user = User::query()
                    ->where('nip', $employeeId)
                    ->first();
            }

            if (! $user && $email !== '') {
                $user = User::query()
                    ->where('email', $email)
                    ->first();
            }

            if (! $user) {
                $user = new User();
            }

            /*
            |--------------------------------------------------------------------------
            | Nama user
            |--------------------------------------------------------------------------
            | SIPO menggunakan firstname dan lastname, bukan kolom name.
            */

            $fullName = trim(
                (string) ($ssoUser['name'] ?? '')
            );

            if ($fullName !== '') {
                $nameParts = preg_split(
                    '/\s+/',
                    $fullName,
                    2
                ) ?: [];

                $firstName = $nameParts[0] ?? null;
                $lastName = $nameParts[1] ?? null;

                if ($firstName) {
                    $user->firstname = $firstName;
                }

                if ($lastName) {
                    $user->lastname = $lastName;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Identitas user
            |--------------------------------------------------------------------------
            */

            if ($employeeId !== '') {
                $user->nip = $employeeId;
            }

            if ($email !== '') {
                $user->email = $email;
            }

            if (Schema::hasColumn('users', 'sso_id')) {
                $user->sso_id = $ssoUser['sso_id']
                    ?? $ssoUser['sub']
                    ?? $user->sso_id;
            }

            /*
            |--------------------------------------------------------------------------
            | Role user SIPO
            |--------------------------------------------------------------------------
            | SIPO menggunakan role_id_role, bukan role_id.
            */

            $user->role_id_role = $this->mapSsoRoleToLocalRoleId(
                roles: $ssoUser['roles'] ?? [],
                currentRoleId: $user->role_id_role
            );

            /*
            |--------------------------------------------------------------------------
            | Password user baru
            |--------------------------------------------------------------------------
            */

            if (! $user->exists || empty($user->password)) {
                $user->password = bcrypt(
                    Str::random(40)
                );
            }

            $user->save();

            Auth::guard('web')->login($user, true);

            $request->session()->regenerate();

            return redirect(
                config(
                    'services.sso.after_login_url',
                    '/dashboard'
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Login NEXID ke SIPO gagal.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Login SSO gagal: '.$exception->getMessage()
                );
        }
    }

    private function mapSsoRoleToLocalRoleId(
        array $roles,
        mixed $currentRoleId = null
    ): int {
        $roles = collect($roles);

        return match (true) {
            $roles->contains('superadmin') => 1,
            $roles->contains('admin') => 2,
            $roles->contains('manager') => 3,
            default => (int) ($currentRoleId ?: 3),
        };
    }
}
