<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Services\Contracts\EquipmentServiceInterface;
use Exception;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    protected EquipmentServiceInterface $equipmentService;

    public function __construct(EquipmentServiceInterface $equipmentService)
    {
        $this->equipmentService = $equipmentService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'assigned_to', 'search', 'category']);
        $page = $request->input('page') ? (int)$request->input('page') : null;
        $perPage = $request->input('per_page') ? (int)$request->input('per_page') : 15;

        $equipment = $this->equipmentService->getPaginatedEquipment($filters, $perPage, $page);
        return $this->paginatedResponse($equipment);
    }

    public function store(StoreEquipmentRequest $request)
    {
        $equipment = $this->equipmentService->createEquipment($request->validated());
        return $this->successResponse($equipment->load('assignee'), null, 201);
    }

    public function show($id)
    {
        $equipment = $this->equipmentService->getEquipmentById((int)$id);

        if (!$equipment) {
            return $this->errorResponse(__('messages.equipment_not_found'), 404);
        }

        return $this->successResponse($equipment);
    }

    public function update(UpdateEquipmentRequest $request, $id)
    {
        $equipment = $this->equipmentService->updateEquipment((int)$id, $request->validated());

        if (!$equipment) {
            return $this->errorResponse(__('messages.equipment_not_found'), 404);
        }

        return $this->successResponse($equipment->load('assignee'));
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->equipmentService->deleteEquipment((int)$id, $request->user());
            return $this->successResponse(null, __('messages.equipment_deleted'));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 403);
        }
    }
}
