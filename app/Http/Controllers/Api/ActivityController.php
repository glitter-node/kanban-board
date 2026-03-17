<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListActivitiesRequest;
use App\Models\Board;
use App\Services\ActivityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class ActivityController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ActivityService $activityService,
    ) {}

    public function index(ListActivitiesRequest $request, Board $board): JsonResponse
    {
        $validated = $request->validated();

        $activities = $this->activityService->listBoardActivities(
            board: $board,
            filters: $validated,
            perPage: $validated['per_page'] ?? 25,
        );

        return response()->json([
            'data' => $activities,
        ]);
    }
}
