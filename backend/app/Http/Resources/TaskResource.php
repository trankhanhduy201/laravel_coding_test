<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    private function prepareAuthorData(): array
    {
        return [
            'id' => $this->author_id ?? $this->author?->id ?? $this->user_id,
            'name' => $this->author_name ?? $this->author?->name,
        ];
    }

    private function prepareAssigneeData(): array
    {
        return [
            'id' => $this->assignee_id ?? $this->assignee?->id ?? $this->assigned_to,
            'name' => $this->assignee_name ?? $this->assignee?->name,
        ];
    }

    private function mapUser(?array $userData, Request $request): ?array
    {
        if (! $userData || empty($userData['id'])) {
            return null;
        }

        return (new UserResource((object) $userData))->toArray($request);
    }

    public function toArray(Request $request): array
    {
        $authorData = $this->prepareAuthorData();
        $assigneeData = $this->prepareAssigneeData();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'author' => $this->mapUser($authorData, $request),
            'assignee' => $this->mapUser($assigneeData, $request),
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
