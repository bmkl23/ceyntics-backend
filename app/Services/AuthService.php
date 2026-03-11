<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(string $email, string $password): array
    {
        // Step 1 — Check credentials
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new \Exception('Invalid email or password', 401);
        }

        // Step 2 — Get authenticated user
        $user = Auth::user();

        // Step 3 — Check if account is active
        if (!$user->is_active) {
            Auth::logout();
            throw new \Exception('Your account has been deactivated. Contact admin.', 403);
        }

        // Step 4 — Revoke old tokens (single session only)
        $user->tokens()->delete();

        // Step 5 — Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function me(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'is_active'  => $user->is_active,
            'created_at' => $user->created_at,
        ];
    }
}