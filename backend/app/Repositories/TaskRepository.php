<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository extends AbstractRepository
{
    protected string $modelClass = Task::class;

    public function getPaginatedWithFilters(array $filters = [], int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        $query = $this->model;

        if (isset($filters['status'])) {
            $query = $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query = $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['user_id'])) {
            $query = $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find(int|string $id)
    {
        return $this->model->find($id);
    }
}
