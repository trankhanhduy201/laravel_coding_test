<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use App\Models\Task;
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

    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }

    public function createTask(array $data): Task
    {
        return $this->taskRepository->create($data);
    }

    public function updateTask(int $id, array $data): ?Task
    {
        return $this->taskRepository->update($id, $data);
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }
}
