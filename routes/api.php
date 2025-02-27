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

Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::apiResource('users', UserController::class);

    Route::post('users/updateimg', [UserController::class,'updateimg']); //Listar

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

    Route::get('abilities', function(Request $request) {
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
});

Route::get('category-list', [CategoryController::class, 'getList']);

Route::get('get-posts', [PostControllerAdvance::class, 'getPosts']);
Route::get('get-category-posts/{id}', [PostControllerAdvance::class, 'getCategoryByPosts']);
Route::get('get-post/{id}', [PostControllerAdvance::class, 'getPost']);


Route::get('notes', [NoteController::class, 'index'])->name('notes.index');
Route::post('notes',[NoteController::class,'store'])->name('notes.store');
Route::get('notes/{id}',[NoteController:: class, 'show'])->name(name: 'notes.show');
Route::put('notes/{id}',[NoteController:: class, 'update'])->name(name: 'notes.update');
Route::delete('notes/{id}',[NoteController:: class, 'destroy'])->name(name: 'notes.destroy');

Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
Route::delete('authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');
Route::get('authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
Route::put('authors/{author}', [AuthorController::class, 'update'])->name('authors.update');

/* -- APP ROUTES -- */

/* - GAMES - */
// Play a public game (ruta personalizada)
Route::post('/games/play-public', [GameController::class, 'playPublicGame']);
// Create a private game (ruta personalizada)
Route::post('/games/create-private', [GameController::class, 'createPrivateGame']);
// Join a private game (ruta personalizada)
Route::get('/games/join-private/{code}', [GameController::class, 'joinPrivateGame']);
// Games (después de la personalizada)
Route::apiResource('games', GameController::class);



// Rankings
Route::apiResource('rankings', RankingController::class);

// Ships
Route::apiResource('ships', ShipController::class);

// Eliminar la ruta duplicada y dejar solo una versión correcta

