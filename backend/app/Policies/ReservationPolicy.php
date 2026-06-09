<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /* Admin can see all, Users can only see their own orders */
    public function viewAny(User user): bool
    {
        return true;
    }

    public function view(User user, Reservation reservation): bool
    {
        return user->role === 'admin' || user->id === $reservation->user_id;
    }

    public function create(User user): bool { return true; }

    public function update(User user, Reservation reservation): bool
    {
        if (user->role === 'admin') return true;

        // User thường chỉ được sửa đơn của chính mình khi nó đang ở trạng thái PENDING
        return user->id === $reservation->user_id && $reservation->status === 'pending';
    }

    public function delete(User user, Reservation reservation): bool
    {
        if (user->role === 'admin') return true;
        return user->id === $reservation->user_id && $reservation->status === 'pending';
    }
}
