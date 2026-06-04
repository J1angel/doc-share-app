<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listExcept(User $user)
    {
        return $this->repository->getAllExceptUserId($user->id);
    }
}
