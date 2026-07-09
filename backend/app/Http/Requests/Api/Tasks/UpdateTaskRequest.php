<?php

namespace App\Http\Requests\Api\Tasks;

use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::enum(TaskStatusEnum::class)],
            'assigned_to' => 'sometimes|integer|exists:users,id',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
