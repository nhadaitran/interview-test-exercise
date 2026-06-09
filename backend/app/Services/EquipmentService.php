<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\User;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use App\Services\Contracts\EquipmentServiceInterface;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EquipmentService implements EquipmentServiceInterface
{
    protected EquipmentRepositoryInterface $equipmentRepo;

    public function __construct(EquipmentRepositoryInterface $equipmentRepo)
    {
        $this->equipmentRepo = $equipmentRepo;
    }

    /**
     * Get paginated equipment with filters.
     *
     * @param array $filters Query parameters like status, assigned_to, search
     * @param int $perPage Number of items per page
     * @param int|null $page Target page number
     * @return LengthAwarePaginator
     */
    public function getPaginatedEquipment(array $filters = [], int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        return $this->equipmentRepo->paginate($filters, $perPage, $page);
    }

    /**
     * Get equipment details by ID.
     *
     * @param int $id
     * @return Equipment|null
     */
    public function getEquipmentById(int $id): ?Equipment
    {
        return $this->equipmentRepo->find($id);
    }

    /**
     * Create new equipment.
     *
     * @param array $data Validated data
     * @return Equipment
     */
    public function createEquipment(array $data): Equipment
    {
        return $this->equipmentRepo->create($data);
    }

    /**
     * Update equipment by ID.
     *
     * @param int $id
     * @param array $data Validated update data
     * @return Equipment|null
     */
    public function updateEquipment(int $id, array $data): ?Equipment
    {
        return $this->equipmentRepo->update($id, $data);
    }

    /**
     * Delete equipment by ID.
     *
     * @param int $id
     * @param User $actor Current logged in user
     * @return bool
     * @throws Exception
     */
    public function deleteEquipment(int $id, User $actor): bool
    {
        if (!$actor->isAdmin()) {
            throw new Exception(__('messages.no_permission'), 403);
        }

        return $this->equipmentRepo->delete($id);
    }
}
