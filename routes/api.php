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

// Avatars
Route::apiResource('avatars', AvatarController::class);


// Ships
Route::get('ships', [ShipController::class, 'index'])->name('ship.index');
Route::post('ships', [ShipController::class, 'store'])->name('ship.store');
Route::get('ships/{id}', [ShipController::class])->name('ship.show');
Route::put('ships/{id}', [ShipController::class])->name('ship.update');
Route::delete('ships/{id}', [ShipController::class])->name('ship.delete');
