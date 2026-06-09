<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    /* All users can see the list and device details */
    public function viewAny(User user): bool { return true; }
    public function view(User user, Equipment equipment): bool { return true; }

    /* Only Admin can intervene and edit the device */
    public function create(User user): bool { return user->role === 'admin'; }
    public function update(User user, Equipment equipment): bool { return user->role === 'admin'; }
    public function delete(User user, Equipment equipment): bool { return user->role === 'admin'; }
}
