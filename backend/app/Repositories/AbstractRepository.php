<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository implements RepositoryInterface
{
    protected string $modelClass;

    protected Model $model;

    public function __construct()
    {
        $this->model = $this->resolveModel();
    }

    protected function resolveModel(): Model
    {
        return new $this->modelClass();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find(int|string $id)
    {
        return $this->model->find($id);
    }

    public function findBy(array $attributes)
    {
        return $this->model->where($attributes)->first();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(int|string $id, array $attributes)
    {
        $record = $this->find($id);

        if (! $record) {
            return null;
        }

        $record->fill($attributes);
        $record->save();

        return $record;
    }

    public function delete(int|string $id): bool
    {
        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        return $record->delete();
    }

    public function firstWhere(string $column, mixed $value)
    {
        return $this->model->where($column, $value)->first();
    }
}
