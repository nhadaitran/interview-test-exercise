<?php

namespace App\Repositories\Contracts;

interface ReservationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15, ?int $page = null, ?int $userId = null);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function hasOverlappingReservation(int $equipmentId, string $startDate, string $endDate, ?int $excludeReservationId = null): bool;
}
