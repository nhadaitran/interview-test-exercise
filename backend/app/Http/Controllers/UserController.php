<?php

namespace App\Http\Controllers;

use App\Services\Contracts\UserServiceInterface;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {
            $users = $this->userService->getAllUsers($request->user());
            return $this->successResponse($users);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 403);
        }
    }
}
