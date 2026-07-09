<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->role === UserRoleEnum::Admin) {
            return true;
        }

        return $user->id === $task->assigned_to || $user->id === $task->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->role === UserRoleEnum::Admin) {
            return true;
        }

        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->role === UserRoleEnum::Admin) {
            return true;
        }

        return $user->id === $task->user_id;
    }
}
