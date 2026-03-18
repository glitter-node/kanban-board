<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ExperimentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/dashboard', function () {
    return redirect()->route('boards.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/analytics/events', [AnalyticsEventController::class, 'store'])
    ->middleware('throttle:api')
    ->name('analytics.events.store');

Route::middleware('auth')->group(function () {
    Route::resource('boards', BoardController::class);
    Route::get('/analytics', [AnalyticsController::class, 'index'])->middleware('verified')->name('analytics.index');
    Route::get('/experiments', [ExperimentController::class, 'index'])->middleware('verified')->name('experiments.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
