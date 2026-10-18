<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            abort(401, 'Email atau password salah');
        }

        if ($user->status !== 'Aktif') {
            abort(403, 'Akun Anda belum aktif. Silakan hubungi admin untuk mengaktifkan akun Anda.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

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
        auth()->user()->currentAccessToken()->delete();

        return (new ApiResource(null))
            ->message('Berhasil logout');
    }
}
