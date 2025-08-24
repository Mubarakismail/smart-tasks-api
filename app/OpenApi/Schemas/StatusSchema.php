<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Status",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="To Do"),
 *   @OA\Property(property="code", type="string", example="to-do"),
 *   @OA\Property(property="order", type="integer", example="1"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-24T10:05:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-24T10:05:00Z")
 * )
 */

class StatusSchema
{

}
