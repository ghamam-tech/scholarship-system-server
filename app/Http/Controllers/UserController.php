<?php

namespace App\Http\Controllers;
use App\Enums\UserRole;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{


    public function createSponsor(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country'   => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['nullable', Password::min(8)], // optional; we can generate one
        ]);
        // Create sponsor user (role=sponsor)
        $user = User::create([
            'email'    => $data['email'],
            'password' => $data['password'] ?? Str::random(12),
            'role'     => UserRole::SPONSOR->value,
        ]);

        // Enforce 1-to-1 sponsor per user (unique user_id at DB)
        $sponsor = Sponsor::create([
            'user_id'   => $user->user_id,
            'name'      => $data['name'],
            'country'   => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Sponsor created',
            'user'    => $user,
            'sponsor' => $sponsor,
        ], 201);
    }
}
