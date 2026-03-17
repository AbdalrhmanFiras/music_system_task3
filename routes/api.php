<?php

use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:api', 'throttle:20,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
Route::post('user/import', [UserController::class, 'userImportFile'])
    ->middleware('auth:api');

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

});
