<?php

namespace App\Services\Contracts;

use App\Models\Reservation;
use App\Models\User;
use Exception;
use Illuminate\Validation\ValidationException;

interface ReservationServiceInterface
{
    /**
     * Create a new equipment reservation request.
     *
     * Check if the target equipment is not under maintenance.
     * Ensure regular users can only reserve available equipment.
     * If the actor is an admin, they can reserve on behalf of another user.
     * Prevent booking if there is an overlapping reservation that is approved or pending.
     *
     * @param array $data Input data containing equipment_id, start_date, end_date, and optionally user_id
     * @param User $actor The user initiating the reservation request
     * @return Reservation Created reservation instance
     * @throws ValidationException If validation fails or conflict occurs
     * @throws Exception If equipment is not found
     */
    public function createReservation(array $data, User $actor): Reservation;

    /**
     * Update an existing reservation request.
     *
     * Allow admin to update dates or status (approve, reject, cancel).
     * Regular users can only update or cancel their own pending reservations.
     * Check for reservation timing conflicts if dates are being modified.
     * If status changes to APPROVED, set the equipment status to reserved and assign it to the reserving user.
     * If status reverts from APPROVED to CANCELLED or REJECTED, make the equipment available again.
     *
     * @param int $id ID of the reservation to update
     * @param array $data Update fields including start_date, end_date, or status
     * @param User $actor The user performing the update
     * @return Reservation Updated reservation instance
     * @throws ValidationException If conflict occurs
     * @throws Exception If reservation is not found or authorization fails
     */
    public function updateReservation(int $id, array $data, User $actor): Reservation;

    /**
     * Delete or cancel a reservation.
     *
     * Admin can delete any reservation at any time.
     * Regular users can only delete their own pending reservations.
     * If deleting an approved reservation, release the associated equipment back to available.
     *
     * @param int $id ID of the reservation to delete
     * @param User $actor The user performing the deletion
     * @return bool True on error, false on failure
     * @throws Exception If reservation is not found or authorization fails
     */
    public function deleteReservation(int $id, User $actor): bool;
}
