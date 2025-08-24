<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Repositories\AuthRepository;

class AuthService
{
    public function __construct(protected AuthRepository $authRepository)
    {
    }

    public function login(array $data)
    {
        $user = $this->authRepository->where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new \InvalidArgumentException('Invalid credentials', 422);
        }
        return $user;
    }

    public function logout($request)
    {
        return $request->user()->currentAccessToken()->delete();
    }

    public function register($request)
    {
        return $this->authRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    }
}
