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


Route::post('forget-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forget.password.post');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::apiResource('users', UserController::class);

    Route::post('users/updateimg', [UserController::class, 'updateimg']); //Listar

    Route::apiResource('posts', PostControllerAdvance::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('roles', RoleController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('/role-permissions', [PermissionController::class, 'updateRolePermissions']);
    Route::apiResource('permissions', PermissionController::class);

    Route::get('category-list', [CategoryController::class, 'getList']);
    Route::get('/user', [ProfileController::class, 'user']);
    Route::put('/user', [ProfileController::class, 'update']);

    Route::get('abilities', function (Request $request) {
        return $request->user()->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    });

    // Modificar esta ruta para que coincida con la URL que estás usando
    Route::post('users/assign-avatar/{id}', [UserController::class, 'assignAvatar'])
        ->name('users.assign-avatar');

    // Avatars
    Route::get('avatars', [AvatarController::class, 'index']);
    Route::apiResource('avatars', AvatarController::class)->except(['index']);

    // Rutas de gestión de avatares de usuario
    Route::get('user-avatars', [UserController::class, 'getUserAvatars']);
    Route::get('users/{userId}/avatars', [UserController::class, 'getUserAvatar']);
    Route::delete('users/{userId}/avatars/{avatarId}', [UserController::class, 'removeAvatar']);

    // Rutas de obtención de posiciones globales y nacionales
    Route::get('/rankings/global-position', [RankingController::class, 'getGlobalPosition']);
    Route::get('/rankings/national-position', [RankingController::class, 'getNationalPosition']);
    // Ruta de obtención de la cantidad de puntos del usuario
    Route::get('/rankings/user-points', [RankingController::class, 'getUserPoints']);
    // Ruta de obtención del historial de partidas
    Route::get('/games/user-match-history', [GameController::class, 'getUserMatchHistory']);
});

Route::get('category-list', [CategoryController::class, 'getList']);

Route::get('get-posts', [PostControllerAdvance::class, 'getPosts']);
Route::get('get-category-posts/{id}', [PostControllerAdvance::class, 'getCategoryByPosts']);
Route::get('get-post/{id}', [PostControllerAdvance::class, 'getPost']);


Route::get('notes', [NoteController::class, 'index'])->name('notes.index');
Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
Route::get('notes/{id}', [NoteController::class, 'show'])->name(name: 'notes.show');
Route::put('notes/{id}', [NoteController::class, 'update'])->name(name: 'notes.update');
Route::delete('notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');

Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
Route::delete('authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');
Route::get('authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
Route::put('authors/{author}', [AuthorController::class, 'update'])->name('authors.update');

/* -- APP ROUTES -- */

/* - GAMES - */
// Get available games for viewing
Route::get('/games/available', [GameController::class, 'getAvailableGames']);
// Check user requirements function
Route::post('/games/check-user-requirements', [GameController::class, 'checkUserRequirements']);
// Join user to game function
Route::post('/games/matchmaking-function', [GameController::class, 'matchmakingFunction']);
// Ship placement function
Route::post('/games/ship-placement-function', [GameController::class, 'shipPlacementFunction']);
// Game play function
Route::post('/games/game-function', [GameController::class, 'gameFunction']);
// Result function
Route::post('/games/result-function', [GameController::class, 'resultFunction']);

// Play a public game (ruta personalizada)
Route::post('/games/play-public', [GameController::class, 'playPublicGame']);
// Create a private game (ruta personalizada)
Route::post('/games/create-private', [GameController::class, 'createPrivateGame']);
// Join a private game (ruta personalizada)
Route::post('/games/join-private', [GameController::class, 'joinPrivateGame']);
// Find match function (nueva ruta)
Route::post('/games/find-match', [GameController::class, 'findMatchFunction']);
// Finish match function
Route::post('/games/finish-match', [GameController::class, 'finishMatchFunction']);
// Check match status
Route::post('/games/check-match-status', [GameController::class, 'checkMatchStatus']);
// Create timestamp
Route::post('/games/create-timestamp', [GameController::class, 'createTimestamp']);
// Check timestamp
Route::post('/games/check-timestamp', [GameController::class, 'checkTimestamp']);
// Get match information
Route::post('/games/get-match-info', [GameController::class, 'getMatchInfo']);


/* -- SHIP PLACEMENT -- */
// Store ship placement
Route::post('/games/store-ship-placement', [GameController::class, 'storeShipPlacement']);
// Store ship placement
Route::post('/games/get-opponent-ship-placement-validation', [GameController::class, 'getOpponentShipPlacementValidation']);
// Get user ship placement
Route::post('/games/get-user-ship-placement', [GameController::class, 'getUserShipPlacement']);

// Games (después de la personalizada)
Route::apiResource('games', GameController::class);

// Rankings
Route::apiResource('rankings', RankingController::class);

// Ships
Route::apiResource('ships', ShipController::class);
Route::get('/ships', [ShipController::class, 'index']);
Route::get('/game-ships', [ShipController::class, 'getGameShips']);
