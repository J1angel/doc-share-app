<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSummaryResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->listExcept($request->user());

        return response()->json([
            'users' => UserSummaryResource::collection($users)->resolve($request),
        ]);
    }
}
