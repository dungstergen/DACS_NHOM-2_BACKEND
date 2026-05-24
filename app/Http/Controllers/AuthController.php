<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'status' => 'active',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        $request->session()->regenerate();

        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
