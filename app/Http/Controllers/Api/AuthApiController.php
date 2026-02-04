<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        // Validasi

        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        // Tentukan apakah login sebagai email atau NIP
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        // Cari user berdasarkan email atau nip
        $user = User::where($field, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Email/NIP atau password salah',
                ],
                401
            );
        }

        // Buat token Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'fullname' => trim($user->firstname . ' ' . $user->lastname),
                'email' => $user->email,
                'nip' => $user->nip,
                'role'  => $user->role_id_role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Semua token dihapus (logout dari semua device)',
        ]);
    }
}
