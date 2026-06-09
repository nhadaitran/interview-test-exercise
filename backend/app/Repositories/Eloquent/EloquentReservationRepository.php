<?php

namespace App\Repositories\Eloquent;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Repositories\Contracts\ReservationRepositoryInterface;

class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15, ?int $page = null, ?int $userId = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Reservation::with(['equipment', 'user']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            if (isset($filters['user_id']) && $filters['user_id'] !== '') {
                $query->where('user_id', $filters['user_id']);
            }
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['equipment_id']) && $filters['equipment_id'] !== '') {
            $query->where('equipment_id', $filters['equipment_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function find(int $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|Reservation|null
    {
        return Reservation::with(['equipment', 'user'])->find($id);
    }

    public function create(array $data)
    {
        return Reservation::create($data);
    }

    public function update(int $id, array $data)
    {
        $resModel = Reservation::find($id);
        if ($resModel) {
            $resModel->update($data);
            return $resModel;
        }
        return null;
    }

    public function delete(int $id)
    {
        $reservation = Reservation::find($id);
        if ($reservation) {
            return $reservation->delete();
        }
        return false;
    }

    public function hasOverlappingReservation(int $equipmentId, string $startDate, string $endDate, ?int $excludeReservationId = null): bool
    {
        $query = Reservation::where('equipment_id', $equipmentId)
            ->whereIn('status', [ReservationStatus::APPROVED, ReservationStatus::PENDING])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeReservationId !== null) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->exists();
    }
}
