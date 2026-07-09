<?php

namespace App\Repositories;

interface RepositoryInterface
{
    public function all();

    public function find(int|string $id);

    public function findBy(array $attributes);

    public function create(array $attributes);

    public function update(int|string $id, array $attributes);

    public function delete(int|string $id): bool;
}
