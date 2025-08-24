<?php

namespace Modules\Task\Repositories\Task;

use Modules\Task\Models\Task;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

class TaskRepository extends BaseRepository implements TaskInterface
{
//    use Auditable;

    protected $fieldSearchable = [
        'title' => 'like',
        'description' => 'like',
        'status.code',
        'status.id',
        'status.name' => 'like',
        'assignee_id',
        'creator_id',
    ];

    public function model(): string
    {
        return Task::class;
    }

    /**
     * @throws RepositoryException
     */
    public function boot(): void
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
