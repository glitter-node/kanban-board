<?php

namespace App\Http\Controllers;

use App\Services\ExperimentService;
use Illuminate\View\View;

class ExperimentController extends Controller
{
    public function __construct(
        private readonly ExperimentService $experimentService,
    ) {}

    public function index(): View
    {
        return view('experiments.index', [
            'metrics' => $this->experimentService->dashboardMetrics(),
        ]);
    }
}
