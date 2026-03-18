<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function index(): View
    {
        return view('analytics.index', [
            'metrics' => $this->analyticsService->dashboardMetrics(),
        ]);
    }
}
