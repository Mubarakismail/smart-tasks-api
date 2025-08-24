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
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskResource;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Info(title="Smart Task API", version="1.0.0")
 *
 * @OA\Server(
 *   url="http://127.0.0.1:8000",
 *   description="Local"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="BearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth", description="Authentication endpoints")
 * @OA\Tag(name="Tasks", description="Task management endpoints")
 *
 * ########################
 * # Component Schemas
 * ########################
 *
 * @OA\Schema(
 *   schema="UserMini",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=4),
 *   @OA\Property(property="name", type="string", example="ahmed mohamed"),
 *   @OA\Property(property="email", type="string", example="ahmed@gmail.com")
 * )
 *
 * @OA\Schema(
 *   schema="TaskStatus",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=2),
 *   @OA\Property(property="code", type="string", example="INPR"),
 *   @OA\Property(property="name", type="string", example="In Progress")
 * )
 *
 * @OA\Schema(
 *   schema="Task",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=3),
 *   @OA\Property(property="title", type="string", example="Hacking Website X"),
 *   @OA\Property(property="description", type="string", nullable=true, example="Testing Store Endpoint"),
 *   @OA\Property(property="status", ref="#/components/schemas/TaskStatus"),
 *   @OA\Property(property="assignee", ref="#/components/schemas/UserMini", nullable=true),
 *   @OA\Property(property="creator", ref="#/components/schemas/UserMini"),
 *   @OA\Property(property="priority", type="integer", example=3),
 *   @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example=null),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-23T15:37:08+00:00"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-23T15:49:06+00:00")
 * )
 *
 * @OA\Schema(
 *   schema="ValidationError",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="The title field is required."),
 *   @OA\Property(
 *     property="errors",
 *     type="object",
 *     additionalProperties=@OA\Schema(type="array", @OA\Items(type="string")),
 *     example={"title": {"The title field is required."}}
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedMeta",
 *   type="object",
 *   @OA\Property(property="current_page", type="integer", example=1),
 *   @OA\Property(property="per_page", type="integer", example=1),
 *   @OA\Property(property="total", type="integer", example=2),
 *   @OA\Property(property="last_page", type="integer", example=2),
 *   @OA\Property(property="next_page_url", type="string", nullable=true, example="http://127.0.0.1:8000/api/v1/tasks?per_page=1&page=2"),
 *   @OA\Property(property="prev_page_url", type="string", nullable=true, example=null)
 * )
 */
class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService)
    {
    }

    /**
     * List all tasks (optionally paginated & searchable).
     *
     * @OA\Get(
     *   path="/api/v1/tasks",
     *   summary="List tasks",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(
     *     name="per_page", in="query", required=false,
     *     description="Enable pagination by setting per_page",
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="search", in="query", required=false,
     *     description="Filter by title using format: title:{text}",
     *     @OA\Schema(type="string", example="title:testing t1")
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       oneOf={
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *           @OA\Property(property="status", type="boolean", example=true),
     *           @OA\Property(property="message", type="string", example="Tasks retrieved successfully."),
     *           @OA\Property(property="meta", ref="#/components/schemas/PaginatedMeta")
     *         ),
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *           @OA\Property(property="status", type="boolean", example=true),
     *           @OA\Property(property="message", type="string", example="Tasks retrieved successfully.")
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        try {
            $tasks = $this->taskService->paginatedOrAll($request);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
            ], $exception->getCode() ?: 500);
        }

        return $this->indexResponse($request, $tasks);
    }

    /**
     * Create a task.
     *
     * @OA\Post(
     *   path="/api/v1/tasks",
     *   summary="Create task",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"title","status_id"},
     *       @OA\Property(property="title", type="string", example="Hacking Website X"),
     *       @OA\Property(property="description", type="string", nullable=true, example="Testing Store Endpoint"),
     *       @OA\Property(property="status_id", type="integer", example=1)
     *     )
     *   ),
     *   @OA\Response(
     *     response=201, description="Created",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task created successfully"),
     *       @OA\Property(property="status", type="string", example="true"),
     *       @OA\Property(property="data", ref="#/components/schemas/Task")
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreTaskRequest $request)
    {
        try {
            $task = $this->taskService->createTask($request->toArray());
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'Task created successfully',
            'status' => 'true',
            'data' => TaskResource::make($task),
        ], 201);
    }

    /**
     * Get a single task by ID.
     *
     * @OA\Get(
     *   path="/api/v1/tasks/{id}",
     *   summary="Show task",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task retrieved successfully"),
     *       @OA\Property(property="status", type="string", example="true"),
     *       @OA\Property(property="data", ref="#/components/schemas/Task")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Record not found."),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show($id)
    {
        try {
            $task = $this->taskService->showTask($id);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Record not found.');
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'Task retrieved successfully',
            'status' => 'true',
            'data' => TaskResource::make($task),
        ]);
    }

    /**
     * Update a task (partial or full).
     *
     * @OA\Put(
     *   path="/api/v1/tasks/{id}",
     *   summary="Update task",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="title", type="string", example="Hacking Website X"),
     *       @OA\Property(property="description", type="string", example="Hacking done successfully"),
     *       @OA\Property(property="status_id", type="integer", example=1, nullable=true)
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task updated successfully"),
     *       @OA\Property(property="status", type="string", example="true"),
     *       @OA\Property(property="data", ref="#/components/schemas/Task")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Record not found."),
     *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(UpdateTaskRequest $request, $id)
    {
        try {
            $task = $this->taskService->updateTask($id, $request->all());
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Record not found.');
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }
        return response()->json([
            'message' => 'Task updated successfully',
            'status' => 'true',
            'data' => TaskResource::make($task),
        ]);
    }

    /**
     * Delete a task.
     *
     * NOTE: Postman collection returns 200 with body, not 204.
     *
     * @OA\Delete(
     *   path="/api/v1/tasks/{id}",
     *   summary="Delete task",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task deleted successfully"),
     *       @OA\Property(property="status", type="string", example="true")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Record not found."),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy($taskId)
    {
        try {
            $this->taskService->deleteTask($taskId);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Record not found.');
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'Task deleted successfully',
            'status' => 'true',
        ], 204);
    }

    /**
     * Assign a task to a user.
     *
     * @OA\Post(
     *   path="/api/v1/tasks/{id}/assign",
     *   summary="Assign task",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"assignee_id"},
     *       @OA\Property(property="assignee_id", type="integer", example=3)
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task assigned successfully"),
     *       @OA\Property(property="status", type="string", example="true"),
     *       @OA\Property(property="data", ref="#/components/schemas/Task")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Record not found."),
     *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function assign(AssignTaskRequest $request, $taskId)
    {
        try {
            $task = $this->taskService->assign($taskId, $request->assignee_id);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Record not found.');
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'Task assigned successfully',
            'status' => 'true',
            'data' => TaskResource::make($task),
        ]);
    }

    /**
     * Change task status.
     *
     * NOTE: Path matches Postman: /change-status
     *
     * @OA\Post(
     *   path="/api/v1/tasks/{id}/change-status",
     *   summary="Change task status",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"status_id"},
     *       @OA\Property(property="status_id", type="integer", example=2)
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task status changed successfully"),
     *       @OA\Property(property="status", type="string", example="true"),
     *       @OA\Property(property="data", ref="#/components/schemas/Task")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Record not found."),
     *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function changeStatus(ChangeTaskStatusRequest $request, $taskId)
    {
        try {
            $task = $this->taskService->changeStatus($taskId, $request->status_id);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Record not found.');
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'Task status changed successfully',
            'status' => 'true',
            'data' => TaskResource::make($task),
        ]);
    }

    /**
     * Restore a soft-deleted task.
     *
     * @OA\Post(
     *   path="/api/v1/tasks/{id}/restore",
     *   summary="Restore task",
     *   tags={"Tasks"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Task restored successfully"),
     *         @OA\Property(property="status", type="string", example="true")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Record not found."),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function restore($taskId)
    {
        try {
            $this->taskService->restoreTask($taskId);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Record not found.');
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'false',
            ], $exception->getCode() ?: 500);
        }

        return response()->json([
            'message' => 'Task restored successfully',
            'status' => 'true',
        ]);
    }

    private function indexResponse($request, $tasks)
    {
        $response = [
            'data' => TaskResource::collection($tasks),
            'status' => true,
            'message' => 'Tasks retrieved successfully.'
        ];

        if ($request->has('per_page')) {
            $response = Arr::add($response, 'meta', [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage(),
                'next_page_url' => $tasks->nextPageUrl(),
                'prev_page_url' => $tasks->previousPageUrl()
            ]);
        }

        return response()->json($response);
    }
}
