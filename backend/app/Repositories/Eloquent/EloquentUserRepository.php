<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function all()
    {
        return User::orderBy('name')->get();
    }

    public function find($id)
    {
        return User::find($id);
    }
}
