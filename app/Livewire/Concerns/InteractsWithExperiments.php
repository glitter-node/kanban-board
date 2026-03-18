<?php

namespace App\Livewire\Concerns;

use App\Services\ExperimentService;

trait InteractsWithExperiments
{
    protected function experimentVariant(string $key): string
    {
        return app(ExperimentService::class)->variant($key, auth()->user());
    }

    protected function experimentIs(string $key, string $variant): bool
    {
        return $this->experimentVariant($key) === $variant;
    }

    protected function experimentAssignments(): array
    {
        return app(ExperimentService::class)->frontendAssignments(auth()->user());
    }
}
