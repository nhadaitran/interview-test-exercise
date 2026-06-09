<?php

namespace App\Repositories\Contracts;

interface EquipmentRepositoryInterface
{
    /**
     * Paginate equipment items with filters and custom page parameter.
     */
    public function paginate(array $filters = [], int $perPage = 15, ?int $page = null);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
