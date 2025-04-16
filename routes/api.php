<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostControllerAdvance;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\ShipController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\UserAvatarController;
use App\Http\Controllers\Api\ChatController;

// --- Public Routes ---
Route::post('forget-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forget.password.post');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');
Route::post('/users', [UserController::class, 'store'])->name('users.store'); // User Registration
Route::get('/rankings', [RankingController::class, 'index']);   // Get all rankings
Route::get('/rankings/national', [RankingController::class, 'getNationalRanking']); // Get national ranking
Route::get('/rankings/global-position', [RankingController::class, 'getGlobalPosition']); // Get global ranking position
Route::get('/rankings/national-position', [RankingController::class, 'getNationalPosition']);   // Get national ranking position
Route::get('/rankings/user-points', [RankingController::class, 'getUserPoints']); // Get user points

// Add any other truly public API routes here

// --- Protected Routes ---
// Apply the 'api' middleware group (for throttling, bindings) AND 'auth:sanctum'
Route::middleware(['api', 'auth:sanctum'])->group(function () {

    // Use apiResource but exclude the 'store' method as it's defined publicly above
    Route::apiResource('users', UserController::class)->except(['store']);

    Route::post('users/updateimg', [UserController::class, 'updateimg']);

    Route::apiResource('posts', PostControllerAdvance::class);
    Route::apiResource('roles', RoleController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    // Route::get('/roles/list', [App\Http\Controllers\Api\RoleController::class, 'getList']); // Duplicate?
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('/role-permissions', [PermissionController::class, 'updateRolePermissions']);
    Route::apiResource('permissions', PermissionController::class);

    Route::get('/user', [ProfileController::class, 'user']);
    Route::put('/user', [ProfileController::class, 'update']);

    Route::get('abilities', function (Request $request) {
        return $request->user()->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->toArray();
    });

    Route::post('/games/view-game-moves', [GameController::class, 'viewGameMoves']);
});
