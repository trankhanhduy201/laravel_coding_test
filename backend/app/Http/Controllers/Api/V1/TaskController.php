<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Tasks\StoreTaskRequest;
use App\Http\Requests\Api\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends ApiController
{
    public function __construct(
        private TaskService $taskService,
        private TaskRepository $taskRepository
    ) {
        $this->authorizeResource(Task::class, 'task');
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getPaginatedTasks($request->query());

        return ApiResponse::success(
            'Tasks retrieved successfully',
            new TaskCollection($tasks),
            Response::HTTP_OK
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskRepository->create($request->validated());

        return ApiResponse::success(
            'Task created successfully',
            new TaskResource($task),
            Response::HTTP_CREATED
        );
    }

    public function show(Task $task): JsonResponse
    {
        $task->loadMissing(['author:id,name', 'assignee:id,name']);

        return ApiResponse::success(
            'Task retrieved successfully',
            new TaskResource($task),
            Response::HTTP_OK
        );
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return ApiResponse::success(
            'Task updated successfully',
            new TaskResource($task),
            Response::HTTP_OK
        );
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return ApiResponse::success(
            'Task deleted successfully'
        );
    }
}