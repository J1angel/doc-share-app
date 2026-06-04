<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public $model = User::class;

    public function getAllExceptUserId(int $userId)
    {
        return $this->query()
            ->where('id', '!=', $userId)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
