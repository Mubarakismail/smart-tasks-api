<?php

namespace Modules\Task\Repositories\Status;

use Modules\Task\Models\Status;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

class StatusRepository extends BaseRepository implements StatusInterface
{

    protected $fieldSearchable = [
        'name' => 'like',
        'code' => 'like',
        'order',
    ];

    public function model(): string
    {
        return Status::class;
    }

    /**
     * @throws RepositoryException
     */
    public function boot(): void
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
