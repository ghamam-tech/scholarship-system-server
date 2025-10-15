<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $data = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => ['sometimes', Password::min(8)],
            'role' => ['in:admin,applicant,sponsor']
        ]);

        $user->update($data);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Ensure the methods are implemented once and correctly.
}
