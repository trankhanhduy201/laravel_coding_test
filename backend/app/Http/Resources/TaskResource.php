<?php

namespace App\Http\Resources;

use App\Support\Concerns\LoadsRelationAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    use LoadsRelationAttributes;

    private function prepareAuthorData(): array
    {
        return [
            'id' => $this->author_id ?? $this->user_id ?? $this->getLoadedRelationAttribute('author', 'id'),
            'name' => $this->author_name ?? $this->getLoadedRelationAttribute('author', 'name'),
        ];
    }

    private function prepareAssigneeData(): array
    {
        return [
            'id' => $this->assignee_id ?? $this->assigned_to ?? $this->getLoadedRelationAttribute('assignee', 'id'),
            'name' => $this->assignee_name ?? $this->getLoadedRelationAttribute('assignee', 'name'),
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
            'user_id' => $this->user_id,
            'assigned_to' => $this->assigned_to,
            'author' => $this->when(
                ! empty($authorData['name']), 
                fn () => $this->mapUser($authorData, $request)
            ),
            'assignee' => $this->when(
                ! empty($assigneeData['name']), 
                fn () => $this->mapUser($assigneeData, $request)
            ),
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
