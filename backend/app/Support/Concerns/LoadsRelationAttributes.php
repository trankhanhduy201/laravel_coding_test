<?php

namespace App\Support\Concerns;

trait LoadsRelationAttributes
{
    protected function getLoadedRelationAttribute(string $relation, string $attribute): mixed
    {
        $resource = data_get($this, 'resource');

        if (! $resource || ! method_exists($resource, 'relationLoaded') || ! $resource->relationLoaded($relation)) {
            return null;
        }

        return $this->{$relation}?->{$attribute};
    }
}
