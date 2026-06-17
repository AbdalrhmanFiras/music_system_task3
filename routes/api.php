<?php

use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\UserController;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:api', 'throttle:20,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:api')->group(function () {

    Route::prefix('artist')->group(function () {
        Route::post('/', [ArtistController::class, 'store']);
        Route::get('/', [ArtistController::class, 'index']);
        Route::get('/{artistId}', [ArtistController::class, 'show']);
        Route::put('/{artistId}', [ArtistController::class, 'update']);
        Route::delete('/{artistId}', [ArtistController::class, 'delete']);
    });

    Route::prefix('categories')->group(function () {

        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('{cateId}', [CategoryController::class, 'show']);
        Route::put('{cateId}', [CategoryController::class, 'update']);
        Route::delete('{cateId}', [CategoryController::class, 'delete']);
    });

    Route::prefix('songs')->group(function () {

        Route::get('/', [SongController::class, 'index']);
        Route::post('/', [SongController::class, 'store']);
        Route::get('{songId}', [SongController::class, 'show']);
        Route::put('{songId}', [SongController::class, 'update']);
        Route::delete('{songId}', [SongController::class, 'delete']);
    });

    Route::prefix('admin')->group(function () {

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{roleId}', [RoleController::class, 'show']);
            Route::put('/{roleId}', [RoleController::class, 'update']);
            Route::delete('/{roleId}', [RoleController::class, 'delete']);
        });

        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::get('/{permissionId}', [PermissionController::class, 'show']);
            Route::put('/{permissionId}', [PermissionController::class, 'update']);
            Route::delete('/{permissionId}', [PermissionController::class, 'delete']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/{userId}/roles', [UserAdminController::class, 'getUserRole']);
            Route::post('/{userId}/assign-role', [UserAdminController::class, 'assignRole']);
            Route::post('/{userId}/assign-permission', [UserAdminController::class, 'assignPermission']);
            Route::put('/{userId}/update-role', [UserAdminController::class, 'updateRole']);
            Route::put('/{userId}/update-permission', [UserAdminController::class, 'updatePermission']);
            Route::post('user/import', [UserController::class, 'userImportFile']);

        });
    });

});

// Include all routes in Scramble documentation (not only routes matching configured api_path)
Scramble::routes(fn ($route) => true);

// Register Scramble API documentation routes (UI and JSON spec)
Scramble::registerUiRoute(path: 'docs/api')->name('scramble.docs.ui');
Scramble::registerJsonSpecificationRoute(path: 'docs/api.json')->name('scramble.docs.document');
