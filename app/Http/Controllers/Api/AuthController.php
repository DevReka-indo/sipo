<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
        $credentials = $request->validate([
            'nip' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::with('role')->where('email', $request->email)->first();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'nip' => $user->nip,
                'email' => $user->email,
                'password' => $user->password,
                'phone_number' => $user->phone_number,
                'role' => $user->role ? $user->role->nm_role : null,
                'position_id_position' => $user->position_id_position,
                'divisi_id_divisi' => $user->divisi_id_divisi,
            ]
        ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkToken(Request $request)
    {
        return response()->json([
            'message' => 'Token is valid'
        ], 200);
    }
}
