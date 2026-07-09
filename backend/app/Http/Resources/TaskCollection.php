<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    public $collects = TaskResource::class;

    public function toArray(Request $request): array
    {
        $meta = [];

        if (method_exists($this->resource, 'currentPage')) {
            $meta = [
                'current_page' => $this->resource->currentPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'last_page' => $this->resource->lastPage(),
            ];
        }

        return [
            'data' => TaskResource::collection($this->collection),
            'meta' => $meta,
        ];
    }
}
