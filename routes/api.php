<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;

Route::get('/health', fn () => response()->json(['ok' => true]));

// Rate limiting for sensitive endpoints (10 requests per minute)
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Projects (CRUD)
    Route::apiResource('projects', ProjectController::class);

    // -------------------------
    // Tasks (GLOBAL) - tests require these
    // -------------------------
    Route::get('/tasks', [TaskController::class, 'index']);           // requires ?project_id=
    Route::post('/tasks', [TaskController::class, 'store']);          // requires project_id in body

    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']); // IMPORTANT for notifications tests
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // -------------------------
    // Tasks (PROJECT-SCOPED)
    // -------------------------
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);   // uses route param
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']); // uses route param

    // -------------------------
    // Comments
    // -------------------------
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);

    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // -------------------------
    // Notifications (rate limited)
    // -------------------------
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('/notifications/unseen', [NotificationController::class, 'unseen']);
        Route::post('/notifications/mark-all-seen', [NotificationController::class, 'markAllSeen']);
        Route::post('/notifications/{id}/mark-seen', [NotificationController::class, 'markOneSeen']);
    });
});
