<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Transformers\UserResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Auth", description="Authentication endpoints")
 */
class AuthController extends Controller
{

    public function __construct(protected AuthService $authService)
    {
    }

    /**
     * Register a new user.
     *
     * @OA\Post(
     *   path="/api/v1/auth/register",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","password","password_confirmation"},
     *       @OA\Property(property="name", type="string", example="Mubarak ismail"),
     *       @OA\Property(property="email", type="string", example="mubarak1@example.com"),
     *       @OA\Property(property="password", type="string", example="secret123"),
     *       @OA\Property(property="password_confirmation", type="string", example="secret123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Registered successfully",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="user created successfully"),
     *       @OA\Property(property="data", ref="#/components/schemas/User"),
     *       @OA\Property(property="access_token", type="string", example="1|tokenstring...")
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register($request);
            $token = $user->createToken($request->input('device_token', 'api'))->plainTextToken;
        } catch (\Exception|\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
            ]);
        }

        return response()->json([
            'message' => 'user created successfully',
            'data' => UserResource::make($user),
            'access_token' => $token
        ], 201);
    }

    /**
     * Login and get a token.
     *
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", example="mubarak1@example.com"),
     *       @OA\Property(property="password", type="string", example="secret123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Logged in",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="user logged successfully"),
     *       @OA\Property(property="data", ref="#/components/schemas/User"),
     *       @OA\Property(property="access_token", type="string", example="1|tokenstring...")
     *     )
     *   ),
     *   @OA\Response(response=401, description="Invalid credentials")
     * )
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->login($request->all());
            $token = $user->createToken($request->input('device_token', 'api'))->plainTextToken;
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'user logged successfully',
            'data' => UserResource::make($user),
            'access_token' => $token
        ]);
    }

    /**
     * Logout current user.
     *
     * @OA\Post(
     *   path="/api/v1/auth/logout",
     *   tags={"Auth"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Logged out",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="user logout successfully")
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'user logout successfully',
            'status' => true
        ]);
    }
}
