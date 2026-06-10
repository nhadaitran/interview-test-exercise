<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Services\Contracts\ReservationServiceInterface;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected ReservationRepositoryInterface $reservationRepo;
    protected ReservationServiceInterface $reservationService;

    public function __construct(
        ReservationRepositoryInterface $reservationRepo,
        ReservationServiceInterface $reservationService
    ) {
        $this->reservationRepo = $reservationRepo;
        $this->reservationService = $reservationService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'equipment_id', 'user_id']);
        $page = $request->input('page') ? (int)$request->input('page') : null;

        $reservations = $this->reservationService->getReservations($filters, $request->user(), $page);
        return $this->paginatedResponse($reservations);
    }

    public function store(StoreReservationRequest $request)
    {
        try {
            $reservation = $this->reservationService->createReservation(
                $request->validated(),
                $request->user()
            );
            return $this->successResponse($reservation->load(['equipment', 'user']), null, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 422);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $reservation = $this->reservationRepo->find((int)$id);

        if (!$reservation) {
            return $this->errorResponse(__('messages.reservation_not_found'), 404);
        }

        if (!$user->isAdmin() && $reservation->user_id !== $user->id) {
            return $this->errorResponse(__('messages.no_permission'), 403);
        }

        return $this->successResponse($reservation);
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        try {
            $reservation = $this->reservationService->updateReservation(
                (int)$id,
                $request->validated(),
                $request->user()
            );
            return $this->successResponse($reservation->load(['equipment', 'user']));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->reservationService->deleteReservation((int)$id, $request->user());
            return $this->successResponse(null, __('messages.reservation_deleted'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 422);
        }
    }
}
