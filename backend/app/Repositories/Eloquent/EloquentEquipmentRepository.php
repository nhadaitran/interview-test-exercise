<?php

namespace App\Repositories\Eloquent;

use App\Models\Equipment;
use App\Repositories\Contracts\EquipmentRepositoryInterface;

class EloquentEquipmentRepository implements EquipmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15, ?int $page = null)
    {
        $query = Equipment::with('assignee');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', 'like', '%' . $filters['category'] . '%');
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to'] !== '') {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('serial_number', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function find(int $id)
    {
        return Equipment::with(['assignee', 'reservations.user'])->find($id);
    }

    public function create(array $data)
    {
        return Equipment::create($data);
    }

    public function update(int $id, array $data)
    {
        $equipment = Equipment::find($id);
        if ($equipment) {
            $equipment->update($data);
            return $equipment;
        }
        return null;
    }

    public function delete(int $id)
    {
        $equipment = Equipment::find($id);
        if ($equipment) {
            return $equipment->delete();
        }
        return false;
    }
}
