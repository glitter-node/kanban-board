<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsEventController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events' => ['required', 'array', 'min:1', 'max:100'],
            'events.*.event_name' => ['required', 'string', 'max:120'],
            'events.*.payload' => ['nullable', 'array'],
            'events.*.created_at' => ['nullable', 'date'],
        ]);

        $this->analyticsService->recordBatch(
            user: $request->user(),
            events: $validated['events'],
        );

        return response()->json([
            'status' => 'ok',
        ], 202);
    }
}
