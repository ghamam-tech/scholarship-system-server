<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sponsor;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Enums\UserRole;

class AuthController extends Controller
{
    /**
     * 🔑 Login (for all users)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create Sanctum token
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * 📝 Register a new Applicant (public)
     * Body: { email, password }
     */
    public function registerApplicant(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = new User();
        $user->email = $data['email'];
        $user->password = $data['password']; // auto-hashed by cast
        $user->role = UserRole::APPLICANT->value;
        $user->save();

        // Create a minimal applicant record (only user_id required)
        Applicant::create([
            'user_id' => $user->user_id,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Applicant registered successfully.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    /**
     * 🧍‍♂️ Create Sponsor (ADMIN ONLY)
     * Body: { name, email, password }
     */
    public function createSponsor(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Create new user with sponsor role
        $user = new User();
        $user->email = $data['email'];
        $user->password = $data['password']; // auto-hash
        $user->role = UserRole::SPONSOR->value;
        $user->save();

        // Create sponsor record
        $sponsor = Sponsor::create([
            'name' => $data['name'],
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'user_id' => $user->user_id,
        ]);

        return response()->json([
            'message' => 'Sponsor account created successfully.',
            'user' => $user,
            'sponsor' => $sponsor,
        ], 201);
    }

    /**
     * 🚪 Logout (delete current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
