<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Task\Http\Requests\AssignTaskRequest;
use Modules\Task\Http\Requests\ChangeTaskStatusRequest;
use Modules\Task\Http\Requests\StoreTaskRequest;
use Modules\Task\Http\Requests\UpdateTaskRequest;
use Modules\Task\Models\Status;
use Modules\Task\Services\StatusService;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\StatusResource;
use Modules\Task\Transformers\TaskResource;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @OA\Tag(name="Statuses", description="Task statuses (lookup) endpoints")
 *
 * @OA\Schema(
 *   schema="StatusSchema",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="In Progress"),
 *   @OA\Property(property="code", type="string", example="INPR"),
 *   @OA\Property(property="order", type="integer", example=2),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-24T10:05:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-24T10:05:00Z")
 * )
 *
 * @OA\Schema(
 *   schema="StatusesPaginatedMeta",
 *   type="object",
 *   @OA\Property(property="current_page", type="integer", example=1),
 *   @OA\Property(property="per_page", type="integer", example=10),
 *   @OA\Property(property="total", type="integer", example=3),
 *   @OA\Property(property="last_page", type="integer", example=1),
 *   @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
 *   @OA\Property(property="prev_page_url", type="string", nullable=true, example=null)
 * )
 */
class StatusController extends Controller
{
    public function __construct(protected StatusService $statusService)
    {
    }

    /**
     * List all statuses (optionally paginated).
     *
     * @OA\Get(
     *   path="/api/v1/statuses",
     *   summary="List statuses",
     *   tags={"Statuses"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     required=false,
     *     description="Enable pagination by setting per_page",
     *     @OA\Schema(type="integer", example=10)
     *   ),
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     required=false,
     *     description="Optional name filter (implementation-specific)",
     *     @OA\Schema(type="string", example="name:TO DO")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Status")
     *       ),
     *       @OA\Property(property="status", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="Statuses retrieved successfully."),
     *       @OA\Property(property="meta", ref="#/components/schemas/PaginatedMeta", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        try {
            $statuses = $this->statusService->paginatedOrAll($request);
        } catch (Exception $exception) {
            dd($exception->getMessage());
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
            ], $exception->getCode() ?: 500);
        }

        return $this->indexResponse($request, $statuses);
    }

    private function indexResponse($request, $statuses)
    {
        $response = [
            'data' => StatusResource::collection($statuses),
            'status' => true,
            'message' => 'Statuses retrieved successfully.'
        ];

        if ($request->has('per_page')) {
            $response = Arr::add($response, 'meta', [
                'current_page' => $statuses->currentPage(),
                'per_page' => $statuses->perPage(),
                'total' => $statuses->total(),
                'last_page' => $statuses->lastPage(),
                'next_page_url' => $statuses->nextPageUrl(),
                'prev_page_url' => $statuses->previousPageUrl()
            ]);
        }

        return response()->json($response);
    }

}
