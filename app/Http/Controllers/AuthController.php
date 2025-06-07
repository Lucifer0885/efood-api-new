<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Device;
use App\Enums\RoleCode;
use App\Models\Role;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved',
            'data' => [
                'user' => $request->user()
            ]
        ], 200);
    }

    public function register(Request $request)
    {
        if ($request->role && $request->role == "admin") {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'string',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'phone' => $fields['phone'] ?? null,
            'password' => bcrypt($fields['password']),
        ]);

        if ($request->role) {
            $role = Role::find(RoleCode::{$request->role});
            if ($role) {
                $user->roles()->attach($role->id);
            }
        }

        $token = $user->createToken(Device::tokenName())->plainTextToken;

        $response = [
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ];

        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($request->role) {
            $role = $user->roles()->where('role_id', RoleCode::{$request->role})->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
        }
        $token = $user->createToken(Device::tokenName())->plainTextToken;

        $response = [
            'success' => true,
            'message' => 'User logged in',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ];

        return response()->json($response);
    }
    public function update(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string|min:10',
        ]);

        $user = $request->user();

        $user->name = $fields['name'];
        $user->phone = $fields['phone'];
        $user->save();

        $response = [
            'success' => true,
            'message' => 'User updated successfully',
            'data' => [
                'user' => $user,
            ]
        ];

        return response()->json($response, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ]);
    }

    public function changePassword(Request $request)
    {
        $fields = $request->validate([
            'current_password' => 'required|string|min:6',
            'password' => 'required|string|min:6',
        ]);

        $user = $request->user();

        if (!Hash::check($fields['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => []
            ], 401);
        }

        if ($fields['current_password'] === $fields['password']) {
            return response()->json([
                'success' => false,
                'message' => 'New password cannot be the same as current password',
                'data' => []
            ], 400);
        }

        $user->password = bcrypt($fields['password']);
        $user->save();

        $response = [
            'success' => true,
            'message' => 'User logged in successfully',
            'data' => [
                'user' => $user,
            ]
        ];

        return response()->json($response, 200);
    }
}
