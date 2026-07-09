<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository extends AbstractRepository
{
    protected string $modelClass = Task::class;

    public function getPaginatedWithFilters(array $filters = [], int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->newQuery()
            ->leftJoin('users as authors', 'authors.id', '=', 'tasks.user_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'tasks.assigned_to')
            ->select([
                'tasks.*',
                'authors.id as author_id',
                'authors.name as author_name',
                'assignees.id as assignee_id',
                'assignees.name as assignee_name',
            ]);

        if (isset($filters['status'])) {
            $query = $query->where('tasks.status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query = $query->where('tasks.assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['user_id'])) {
            $query = $query->where('tasks.user_id', $filters['user_id']);
        }

        if (isset($filters['title'])) {
            $query = $query->where('tasks.title', 'like', '%' . $filters['title'] . '%');
        }

        $query = $query->orderByDesc('tasks.created_at');

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
