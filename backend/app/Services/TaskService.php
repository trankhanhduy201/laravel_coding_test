<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function getAllTasks(array $queryParams = []): Collection
    {
        $filters = $this->prepareFilters($queryParams);
        return $this->taskRepository->getAllWithFilters($filters);
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
