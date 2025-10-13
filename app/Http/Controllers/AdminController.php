<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admin::with('user')->get();
        return response()->json($admins);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', Password::min(8)],
        ]);

        // Create admin user (role=admin)
        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'] ?? Str::random(12),
            'role' => UserRole::ADMIN->value,
        ]);

        // Create admin profile
        $admin = Admin::create([
            'user_id' => $user->user_id,
            'name' => $data['name'],
        ]);

        return response()->json([
            'message' => 'Admin created successfully',
            'user' => $user,
            'admin' => $admin,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        $admin->load('user');
        return response()->json($admin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $admin->user_id . ',user_id'],
            'password' => ['sometimes', 'nullable', Password::min(8)],
        ]);

        // Update user if email or password provided
        if (isset($data['email']) || isset($data['password'])) {
            $userData = [];
            if (isset($data['email'])) {
                $userData['email'] = $data['email'];
            }
            if (isset($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $admin->user->update($userData);
        }

        // Update admin profile
        if (isset($data['name'])) {
            $admin->update(['name' => $data['name']]);
        }

        $admin->load('user');
        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => $admin,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        $admin->user->delete(); // This will cascade delete the admin due to foreign key constraint
        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
