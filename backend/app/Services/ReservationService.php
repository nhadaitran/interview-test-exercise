<?php

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Services\Contracts\ReservationServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReservationService implements ReservationServiceInterface
{
    protected ReservationRepositoryInterface $reservationRepo;
    protected EquipmentRepositoryInterface $equipmentRepo;

    public function __construct(
        ReservationRepositoryInterface $reservationRepo,
        EquipmentRepositoryInterface $equipmentRepo
    ) {
        $this->reservationRepo = $reservationRepo;
        $this->equipmentRepo = $equipmentRepo;
    }

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
    public function createReservation(array $data, User $actor): Reservation
    {
        $equipment = $this->equipmentRepo->find($data['equipment_id']);

        if (!$equipment) {
            throw new Exception(__('messages.equipment_not_found'), 404);
        }

        if ($equipment->status === EquipmentStatus::MAINTENANCE) {
            throw ValidationException::withMessages([
                'equipment_id' => [__('messages.maintenance_status')],
            ]);
        }

        if (!$actor->isAdmin() && $equipment->status !== EquipmentStatus::AVAILABLE) {
            throw ValidationException::withMessages([
                'equipment_id' => [__('messages.not_available_status')],
            ]);
        }

        $resUserId = $actor->isAdmin() ? ($data['user_id'] ?? $actor->id) : $actor->id;

        $hasConflict = $this->reservationRepo->hasOverlappingReservation(
            $equipment->id,
            $data['start_date'],
            $data['end_date']
        );

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_date' => [__('messages.conflict_status')],
            ]);
        }

        return $this->reservationRepo->create([
            'equipment_id' => $equipment->id,
            'user_id' => $resUserId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => ReservationStatus::PENDING,
        ]);
    }

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
     * @throws Exception|Throwable If reservation is not found or authorization fails
     */
    public function updateReservation(int $id, array $data, User $actor): Reservation
    {
        $reservation = $this->reservationRepo->find($id);

        if (!$reservation) {
            throw new Exception(__('messages.reservation_not_found'), 404);
        }

        if (!$actor->isAdmin()) {
            if ($reservation->user_id !== $actor->id) {
                throw new Exception(__('messages.no_permission'), 403);
            }
            if ($reservation->status !== ReservationStatus::PENDING) {
                throw new Exception(__('messages.only_edit_pending'), 422);
            }
        }

        if (!$actor->isAdmin() && isset($data['status']) && $data['status'] !== ReservationStatus::CANCELLED->value) {
            throw new Exception(__('messages.only_cancel_own'), 422);
        }

        $startDate = $data['start_date'] ?? $reservation->start_date->format('Y-m-d');
        $endDate = $data['end_date'] ?? $reservation->end_date->format('Y-m-d');

        if (isset($data['start_date']) || isset($data['end_date'])) {
            $hasConflict = $this->reservationRepo->hasOverlappingReservation(
                $reservation->equipment_id,
                $startDate,
                $endDate,
                $reservation->id
            );

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'start_date' => [__('messages.conflict_status')],
                ]);
            }
        }

        return DB::transaction(function () use ($reservation, $data) {
            $oldStatus = $reservation->status;
            $updatedReservation = $this->reservationRepo->update($reservation->id, $data);
            $newStatus = $updatedReservation->status;

            if ($oldStatus !== $newStatus) {
                $equipment = $updatedReservation->equipment;

                if ($newStatus === ReservationStatus::APPROVED) {
                    $this->equipmentRepo->update($equipment->id, [
                        'status' => EquipmentStatus::RESERVED,
                        'assigned_to' => $updatedReservation->user_id,
                        'due_date' => $updatedReservation->end_date,
                    ]);
                } elseif (
                    $oldStatus === ReservationStatus::APPROVED &&
                    in_array($newStatus, [ReservationStatus::CANCELLED, ReservationStatus::REJECTED])
                ) {
                    $this->equipmentRepo->update($equipment->id, [
                        'status' => EquipmentStatus::AVAILABLE,
                        'assigned_to' => null,
                        'due_date' => null,
                    ]);
                }
            }

            return $updatedReservation;
        });
    }

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
     * @throws Exception|Throwable If reservation is not found or authorization fails
     */
    public function deleteReservation(int $id, User $actor): bool
    {
        $reservation = $this->reservationRepo->find($id);

        if (!$reservation) {
            throw new Exception(__('messages.reservation_not_found'), 404);
        }

        if (!$actor->isAdmin()) {
            if ($reservation->user_id !== $actor->id) {
                throw new Exception(__('messages.no_permission'), 403);
            }
            if ($reservation->status !== ReservationStatus::PENDING) {
                throw new Exception(__('messages.only_edit_pending'), 422);
            }
        }

        return DB::transaction(function () use ($reservation) {
            if ($reservation->status === ReservationStatus::APPROVED) {
                $this->equipmentRepo->update($reservation->equipment_id, [
                    'status' => EquipmentStatus::AVAILABLE,
                    'assigned_to' => null,
                    'due_date' => null,
                ]);
            }
            return $this->reservationRepo->delete($reservation->id);
        });
    }

    /**
     * Get paginated reservations with filters.
     *
     * @param array $filters Filters for the list (status, equipment_id, user_id)
     * @param User $actor The user requesting the reservations
     * @param int|null $page Page number
     * @param int $perPage Items per page
     * @return mixed Paginated reservations
     */
    public function getReservations(array $filters, User $actor, ?int $page = null, int $perPage = 15): mixed
    {
        $userId = $actor->isAdmin() ? null : $actor->id;
        return $this->reservationRepo->paginate($filters, $perPage, $page, $userId);
    }
}
