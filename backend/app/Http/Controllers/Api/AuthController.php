<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\LoginRequest;
use App\Repositories\UserRepository;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function __construct(
        protected UserRepository $userRepo
    ) {}
    
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = $this->userRepo->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success('Login successful', [
            'token' => $token,
            'user' => $user,
        ]);
    }
}
