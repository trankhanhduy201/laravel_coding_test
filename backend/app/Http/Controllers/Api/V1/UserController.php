<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->query('q', ''));
        $limit = $request->query('limit');
        $resolvedLimit = is_numeric($limit) ? (int) $limit : null;

        $users = $this->userRepository->searchForLookup($keyword, $resolvedLimit);

        return ApiResponse::success(
            'Users retrieved successfully',
            UserResource::collection($users)->resolve()
        );
    }
}
