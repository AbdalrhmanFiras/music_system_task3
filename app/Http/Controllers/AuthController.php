<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());
        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'User registed successfully', 'data' => new UserResource($user), 'token' => $token], 201);

    }

    public function login(LoginRequest $request)
    {

        if (! $token = JWTAuth::attempt($request->validated())) {
            return response()->json('Invalid email or password', 401);
        }

        return response()->json(['message' => 'User logged in  successfully',
            'data' => new UserResource(JWTAuth::user()), 'token' => $token], 200);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'User logged out successfully'], 200);
    }

    public function me()
    {
        return new UserResource(JWTAuth::user());
    }
}
