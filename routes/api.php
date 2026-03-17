<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\BoardMemberController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/boards', [BoardController::class, 'index']);
    Route::post('/boards', [BoardController::class, 'store']);
    Route::get('/boards/{board}', [BoardController::class, 'show']);
    Route::patch('/boards/{board}', [BoardController::class, 'update']);
    Route::delete('/boards/{board}', [BoardController::class, 'destroy']);

    Route::prefix('/boards/{board}')->group(function () {
        Route::get('/members', [BoardMemberController::class, 'index']);
        Route::post('/members', [BoardMemberController::class, 'store']);
        Route::patch('/members/{member}', [BoardMemberController::class, 'update']);
        Route::delete('/members/{member}', [BoardMemberController::class, 'destroy']);

        Route::get('/columns', [ColumnController::class, 'index']);
        Route::post('/columns', [ColumnController::class, 'store']);
        Route::patch('/columns/{column}', [ColumnController::class, 'update']);
        Route::delete('/columns/{column}', [ColumnController::class, 'archive']);
        Route::post('/columns/reorder', [ColumnController::class, 'reorder']);

        Route::get('/cards', [CardController::class, 'index']);
        Route::post('/cards', [CardController::class, 'store']);
        Route::get('/cards/{card}', [CardController::class, 'show']);
        Route::patch('/cards/{card}', [CardController::class, 'update']);
        Route::delete('/cards/{card}', [CardController::class, 'archive']);
        Route::post('/cards/{card}/move', [CardController::class, 'move']);
        Route::post('/cards/{card}/archive', [CardController::class, 'archive']);
        Route::post('/cards/{card}/assign', [CardController::class, 'assign']);

        Route::get('/cards/{card}/comments', [CommentController::class, 'index']);
        Route::post('/cards/{card}/comments', [CommentController::class, 'store']);
        Route::patch('/cards/{card}/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/cards/{card}/comments/{comment}', [CommentController::class, 'destroy']);

        Route::get('/activities', [ActivityController::class, 'index']);
    });

    Route::get('/me/notifications', [NotificationController::class, 'index']);
});
