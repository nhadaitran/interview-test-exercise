<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserServiceInterface
{
    /**
     * Get all users.
     *
     * @param User $actor Current logged in user
     * @return Collection
     */
    public function getAllUsers(User $actor): Collection;
}
