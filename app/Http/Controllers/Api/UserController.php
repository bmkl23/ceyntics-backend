<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index()
    {
        return response()->json($this->userService->getAllUsers());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,staff',
        ]);

        try {
            $user = $this->userService->createUser($request->all(), auth()->id());
            return response()->json(['message' => 'User created', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $id,
            'role'      => 'sometimes|in:admin,staff',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $updated = $this->userService->updateUser($user, $request->all());
            return response()->json(['message' => 'User updated', 'user' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(string $id)
{
    $user = User::findOrFail($id);
    try {
        $user->forceDelete(); // or just $user->delete()
        return response()->json(['message' => 'User deleted']);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }
}

}