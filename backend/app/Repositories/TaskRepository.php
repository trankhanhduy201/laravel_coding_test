<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository extends AbstractRepository
{
    protected string $modelClass = Task::class;

    public function getAllWithFilters(array $filters = []): Collection
    {
        $query = $this->model;

        if (isset($filters['status'])) {
            $query = $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query = $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->get();
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
