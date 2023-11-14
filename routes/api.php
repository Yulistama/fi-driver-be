<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingStaffController;
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
    Route::put('/users/update', [\App\Http\Controllers\UserController::class, 'update'])->name('user.update');
    Route::get('/users/get', [\App\Http\Controllers\UserController::class, 'get'] )->name('user.get');
    Route::get('/users/logout', [\App\Http\Controllers\UserController::class, 'logout'] )->name('logout');

    Route::post('/booking/create', [\App\Http\Controllers\BookingStaffController::class, 'create'])->name('booking.create');
    Route::get('/booking/staff/active', [\App\Http\Controllers\BookingStaffController::class, 'getAllByActive'])->name('booking.staff.active');
    Route::get('/booking/staff/history', [\App\Http\Controllers\BookingStaffController::class, 'getAllByHistory'])->name('booking.staff.history');
    Route::get('/booking/staff/{idBooking}', [\App\Http\Controllers\BookingStaffController::class, 'getById'])->name('booking.staff.detail');
    Route::put('/booking/staff/update/{idBooking}', [\App\Http\Controllers\BookingStaffController::class, 'update'])->name('booking.staff.update');

    Route::get('/booking/driver/active', [\App\Http\Controllers\BookingDriverController::class, 'getAllByActive'])->name('booking.driver.active');
    Route::get('/booking/driver/history', [\App\Http\Controllers\BookingDriverController::class, 'getAllByHistory'])->name('booking.driver.history');
    Route::get('/booking/driver/{idBooking}', [\App\Http\Controllers\BookingDriverController::class, 'getById'])->name('booking.driver.detail');
    Route::put('/booking/driver/update/{idBooking}', [\App\Http\Controllers\BookingDriverController::class, 'update'])->name('booking.driver.update');

    Route::get('/booking/admin/all', [\App\Http\Controllers\BookingAdminController::class, 'getAll'])->name('booking.admin.all');
    Route::get('/booking/admin/detail/{idBooking}', [\App\Http\Controllers\BookingAdminController::class, 'getById'])->name('booking.admin.detail');
    Route::put('/booking/admin/update/{idBooking}', [\App\Http\Controllers\BookingAdminController::class, 'update'])->name('booking.admin.update');
});

Route::get('/not/token',  function (Request $request) {
    return response()->json([
        'status' => false,
        'message' => 'Unauthenticated'
    ], 401);
})->name('not.token');
