<?php

namespace Modules\Task\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Task\Repositories\Status\StatusRepository;

class StatusService
{
    public function __construct(protected StatusRepository $statusRepository, protected Cache $cache)
    {
    }

    public function paginatedOrAll($request)
    {
        $key = 'statuses:index:' . md5(serialize($request->all()));
        return $this->cache->remember($key, 60, function () use ($request) {
            $query = $this->statusRepository->orderBy('updated_at', 'desc');
            if ($request->has('per_page')) {
                return $query->paginate($request->per_page ?? 10);
            }
            return $query->get();
        });
    }

}
