<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function getPaginatedTasks(array $queryParams = []): LengthAwarePaginator
    {
        $filters = $this->prepareFilters($queryParams);
        $page = (int) ($queryParams['page'] ?? config('pagination.default_page'));
        $limit = (int) ($queryParams['limit'] ?? config('pagination.default_limit'));
        
        return $this->taskRepository->getPaginatedWithFilters($filters, $page, $limit);
    }

    private function prepareFilters(array $queryParams): array
    {
        $filters = [];

        if (! empty($queryParams['status'] ?? null)) {
            $filters['status'] = $queryParams['status'];
        }

        if (! empty($queryParams['assigned_to'] ?? null)) {
            $filters['assigned_to'] = $queryParams['assigned_to'];
        }

        return $filters;
    }
}
