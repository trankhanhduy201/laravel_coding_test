<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends AbstractRepository
{
    protected string $modelClass = User::class;

    public function findByEmail(string $email)
    {
        return $this->firstWhere('email', $email);
    }
}
