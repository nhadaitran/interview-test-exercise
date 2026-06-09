<?php

namespace App\Services\Contracts;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EquipmentServiceInterface
{
    /**
     * Get paginated equipment with filters.
     *
     * @param array $filters Query parameters like status, assigned_to, search
     * @param int $perPage Number of items per page
     * @param int|null $page Target page number
     * @return LengthAwarePaginator
     */
    public function getPaginatedEquipment(array $filters = [], int $perPage = 15, ?int $page = null): LengthAwarePaginator;

    /**
     * Get equipment details by ID.
     *
     * @param int $id
     * @return Equipment|null
     */
    public function getEquipmentById(int $id): ?Equipment;

    /**
     * Create new equipment.
     *
     * @param array $data Validated data
     * @return Equipment
     */
    public function createEquipment(array $data): Equipment;

    /**
     * Update equipment by ID.
     *
     * @param int $id
     * @param array $data Validated update data
     * @return Equipment|null
     */
    public function updateEquipment(int $id, array $data): ?Equipment;

    /**
     * Delete equipment by ID.
     *
     * @param int $id
     * @param User $actor Current logged in user
     * @return bool
     */
    public function deleteEquipment(int $id, User $actor): bool;
}
