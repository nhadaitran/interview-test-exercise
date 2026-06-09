<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Get all users.
     *
     * @param User $actor Current logged in user
     * @return Collection
     * @throws Exception
     */
    public function getAllUsers(User $actor): Collection
    {
        if (!$actor->isAdmin()) {
            throw new Exception(__('messages.no_permission'), 403);
        }

        return $this->userRepo->all();
    }
}
