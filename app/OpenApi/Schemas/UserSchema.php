<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Mubarak ismail"),
 *   @OA\Property(property="email", type="string", example="Mubarak1@example.com"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-24T10:05:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-24T10:05:00Z")
 * )
 */
class UserSchema
{
}
