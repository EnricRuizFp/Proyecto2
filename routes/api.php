<?php

use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\ShipController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\UserAvatarController;
use App\Http\Controllers\Api\ChatController;


Route::post('forget-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forget.password.post');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');

Route::group(['middleware' => 'auth:sanctum'], function () {

    // Obtener TODOS los usuarios (sin paginación)
    Route::get('users/all', [UserController::class, 'getAllUsers']);
    Route::apiResource('users', UserController::class);

    Route::post('users/updateimg', [UserController::class, 'updateimg']); //Listar

    Route::apiResource('roles', RoleController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('/roles/list', [App\Http\Controllers\Api\RoleController::class, 'getList']);
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

    // Route for getting the national ranking list (NEW)
    Route::get('/rankings/national', [RankingController::class, 'getNationalRanking']);

    // Rankings - Admin specific route
    Route::get('rankings/admin', [RankingController::class, 'indexAdmin']);

    // Ruta de obtención del historial de partidas (authenticated version)
    Route::get('/games/user-match-history', [GameController::class, 'getUserMatchHistory']);

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
    // Get last move
    Route::post('/games/get-last-move', [GameController::class, 'getLastMove']);
    // Set game winner
    Route::post('/games/set-game-winner', [GameController::class, 'setGameWinner']);

    /* -- GAME PLAY -- */
    // Attack function
    Route::post('/games/attack', [GameController::class, 'attackPosition']);
    // Get user moves
    Route::post('/games/get-user-moves', [GameController::class, 'getUserMoves']);
    // Get game state
    Route::post('/games/get-opponent-ship-placement-game', [GameController::class, 'getOpponentShipPlacementGame']);
    // Set game ending
    Route::post('/games/set-game-ending', [GameController::class, 'setGameEnding']);

    /* -- SHIP PLACEMENT -- */
    // Store ship placement
    Route::post('/games/store-ship-placement', [GameController::class, 'storeShipPlacement']);
    Route::post('/games/get-opponent-ship-placement-validation', [GameController::class, 'getOpponentShipPlacementValidation']);
    Route::post('/games/get-user-ship-placement', [GameController::class, 'getUserShipPlacement']);

    // Nueva ruta para obtener todos los juegos
    Route::get('/games/all', [GameController::class, 'getAllGames']);

    // Games (después de la personalizada)
    Route::apiResource('games', GameController::class);

    // Ships
    Route::apiResource('ships', ShipController::class);
    Route::get('/ships', [ShipController::class, 'index']);
    Route::get('/game-ships', [ShipController::class, 'getGameShips']);
    Route::get('/game-ships', [GameController::class, 'getAvailableGameShips']);

    /* -- CHAT FUNCTIONS -- */
    Route::post('/games/chat/get-messages', [ChatController::class, 'getMessages']);
    Route::post('/games/chat/send-message', [ChatController::class, 'sendMessage']);

    /* -- GAME VIEW FUNCTIONS -- */
    // Get current match status
    Route::post('/games/get-current-match-status', [GameController::class, 'getCurrentMatchStatus']);
    // Join a match as observer
    Route::post('/games/view-game', [GameController::class, 'viewGame']);
    // Get game moves and status for viewers
    Route::post('/games/view-game-moves', [GameController::class, 'viewGameMoves']);

    // Nueva ruta para obtener todos los rankings
    Route::get('/rankings/all', [RankingController::class, 'getAllRankings']);
});

// Rankings routes outside auth:sanctum
Route::post('/rankings', [RankingController::class, 'store'])->name('rankings.store');
Route::get('/rankings/{id}', [RankingController::class, 'show'])->name('rankings.show');
Route::get('/rankings', [RankingController::class, 'index'])->name('rankings.index');
Route::put('/rankings/{id}', [RankingController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/rankings/{id}', [RankingController::class, 'destroy'])->middleware('auth:sanctum');
