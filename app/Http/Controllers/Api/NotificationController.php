<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListNotificationsRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $notifications = $this->notificationService->listNotifications(
            recipient: $request->user(),
            perPage: $request->validated('per_page') ?? 25,
        );

        return response()->json([
            'data' => $notifications,
        ]);
    }
}
