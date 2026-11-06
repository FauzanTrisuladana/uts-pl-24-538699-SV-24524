<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Api untuk login user dengan email dan password.
     * Post /api/auth/login
     */
    public function login(LoginRequest $request): ApiResource
    {
        $validated = $request->validated();

        $user = User::withTrashed()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            Log::warning('Percobaan login gagal: kredensial tidak valid', [
                'who' => [
                    'type' => 'guest',
                    'client_ip' => $request->ip(),
                ],
                'action' => 'POST /api/auth/login',
                'reason' => 'Email tidak ditemukan atau password tidak cocok',
            ]);

            abort(401, 'Email atau password salah');
        }

        if ($user->status !== 'Aktif') {
            Log::warning('Percobaan login gagal: akun tidak aktif', [
                'who' => [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'client_ip' => $request->ip(),
                ],
                'action' => 'POST /api/auth/login',
                'reason' => 'Akun pengguna belum berstatus Aktif',
            ]);

            abort(403, 'Akun Anda belum aktif. Silakan hubungi admin untuk mengaktifkan akun Anda.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Pengguna berhasil login', [
            'who' => [
                'user_id' => $user->id,
                'role' => $user->role,
            ],
            'action' => 'POST /api/auth/login',
            'reason' => 'Kredensial valid dan token sesi berhasil diterbitkan',
        ]);

        return (new UserResource($user))->additional([
            'auth' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ])
            ->message('Login berhasil');
    }

    /**
     * Api untuk logout user.
     * Post /api/auth/logout
     */
    public function logout(): ApiResource
    {
        $user = auth()->user();

        Log::info('Pengguna berhasil logout', [
            'who' => [
                'user_id' => $user?->id,
                'role' => $user?->role,
            ],
            'action' => 'POST /api/auth/logout',
            'reason' => 'Pengguna mengakhiri sesi dan mencabut access token',
        ]);

        auth()->user()->currentAccessToken()->delete();

        return (new ApiResource(null))
            ->message('Berhasil logout');
    }
}
