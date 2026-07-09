<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends AbstractRepository
{
    protected string $modelClass = User::class;

    public function findByEmail(string $email)
    {
        return $this->firstWhere('email', $email);
    }

    public function searchForLookup(string $keyword = '', ?int $limit = null): Collection
    {
        $defaultLimit = (int) config('user_lookup.default_limit', 20);
        $maxLimit = (int) config('user_lookup.max_limit', 50);
        $resolvedLimit = $limit ?? $defaultLimit;
        $resolvedLimit = max(1, min($resolvedLimit, $maxLimit));

        return $this->model
            ->newQuery()
            ->select(['id', 'name'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            })
            ->orderBy('name')
            ->limit($resolvedLimit)
            ->get();
    }
}
