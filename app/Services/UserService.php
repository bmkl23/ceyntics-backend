<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAllUsers(): object
    {
        return User::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createUser(array $data, int $createdBy): User
    {
        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
            'role'       => $data['role'],
            'is_active'  => true,
            'created_by' => $createdBy,
        ]);

        AuditLogService::log(
            'user.created',
            'User',
            $user->id,
            [],
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            "User {$user->name} created"
        );

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $oldValues = $user->only(['name', 'email', 'role', 'is_active']);

        $user->update(array_filter([
            'name'      => $data['name']      ?? null,
            'email'     => $data['email']     ?? null,
            'role'      => $data['role']      ?? null,
            'is_active' => $data['is_active'] ?? null,
        ], fn($v) => !is_null($v)));

        AuditLogService::log(
            'user.updated',
            'User',
            $user->id,
            $oldValues,
            $user->only(['name', 'email', 'role', 'is_active']),
            "User {$user->name} updated"
        );

        return $user->fresh();
    }

    public function deactivateUser(User $user, int $requestingUserId): void
    {
        if ($user->id === $requestingUserId) {
            throw new \Exception('Cannot deactivate your own account', 400);
        }

        $user->update(['is_active' => false]);

        AuditLogService::log(
            'user.deactivated',
            'User',
            $user->id,
            ['is_active' => true],
            ['is_active' => false],
            "User {$user->name} deactivated"
        );
    }
}