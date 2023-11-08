<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/users/login', [\App\Http\Controllers\UserController::class, 'login'] )->name('login');

Route::middleware('auth:sanctum', 'ability:*')->group(function () {
    Route::post('/users/create', [\App\Http\Controllers\UserController::class, 'register'])->name('user.create');
    Route::get('/users/get', [\App\Http\Controllers\UserController::class, 'get'] )->name('user.get');
    Route::get('/users/logout', [\App\Http\Controllers\UserController::class, 'logout'] )->name('logout');
});

Route::get('/not/token',  function (Request $request) {
    return response()->json([
        'status' => false,
        'message' => 'Unauthenticated'
    ], 401);
})->name('not.token');
