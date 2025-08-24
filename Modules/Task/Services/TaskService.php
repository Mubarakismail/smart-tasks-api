<?php

namespace Modules\Task\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Task\Repositories\Task\TaskRepository;

class TaskService
{
    public function __construct(protected TaskRepository $taskRepository, protected Cache $cache)
    {
    }

    public function paginatedOrAll($request)
    {
        $key = 'tasks:index:' . md5(serialize($request->all()));
        return $this->cache->remember($key, 60, function () use ($request) {
            $query = $this->taskRepository->with(['status', 'assignee:id,name', 'creator:id,name'])->orderBy('created_at', 'desc');
            if ($request->has('per_page')) {
                return $query->paginate($request->per_page ?? 10);
            }
            return $query->get();
        });
    }

    public function createTask($data)
    {
        $task = $this->taskRepository->firstOrCreate($data);
        $task->writeAudit('created');
        return $task->load(['status', 'assignee:id,name', 'creator:id,name']);
    }

    public function showTask($taskId)
    {
        $task = $this->taskRepository->findOrFail($taskId);
        return $task->load(['status', 'assignee:id,name', 'creator:id,name']);
    }

    public function updateTask($taskId, $data)
    {
        $task = $this->taskRepository->findOrFail($taskId);
        $task->writeAudit('updated', $task->toArray(), $data);
        $task->update($data);
        return $task->load('status');
    }

    public function assign($task_id, $assignee_id)
    {
        $task = $this->taskRepository->findOrFail($task_id);
        $task->writeAudit('assigned', ['assignee_id' => $task->getOriginal('assignee_id')], ['assignee_id' => $assignee_id]);
        $task->update(['assignee_id' => $assignee_id]);
        return $task->load(['status', 'assignee:id,name', 'creator:id,name']);
    }

    public function changeStatus($task_id, $statusId)
    {
        $old = $this->taskRepository->first($task_id)->getOriginal('status_id');
        $task = $this->taskRepository->update(['status_id' => $statusId], $task_id);
        $task->writeAudit('status_changed', ['status_id' => $old], ['status_id' => $statusId]);
        return $task->load('status');
    }

    public function deleteTask($task_id)
    {
        $task = $this->taskRepository->findOrFail($task_id);
        $task->writeAudit('deleted');
        return $task->delete();
    }

    public function restoreTask($task_id)
    {
        $task = $this->taskRepository->withTrashed()->findOrFail($task_id);
        $task->writeAudit('restored');
        return $task->restore();
    }
}
