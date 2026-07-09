<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tasks\StoreTaskRequest;
use App\Http\Requests\Api\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getAllTasks($request->query());

        return ApiResponse::success(
            'Tasks retrieved successfully',
            new TaskCollection($tasks),
            Response::HTTP_OK
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return ApiResponse::success(
            'Task created successfully',
            new TaskResource($task),
            Response::HTTP_CREATED
        );
    }

    public function show(Task $task): JsonResponse
    {
        return ApiResponse::success(
            'Task retrieved successfully',
            new TaskResource($task),
            Response::HTTP_OK
        );
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTask($task->id, $request->validated());

        return ApiResponse::success(
            'Task updated successfully',
            new TaskResource($updatedTask),
            Response::HTTP_OK
        );
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task->id);

        return ApiResponse::success(
            'Task deleted successfully',
            [],
            Response::HTTP_OK
        );
    }
}