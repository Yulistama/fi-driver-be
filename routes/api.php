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
Route::post('/users/forgot-password', [\App\Http\Controllers\UserController::class, 'forgotPassword'] )->name('forgot.password');

Route::middleware('auth:sanctum', 'ability:*')->group(function () {
    Route::post('/users/create', [\App\Http\Controllers\UserController::class, 'register'])->name('user.create');
    Route::post('/users/update', [\App\Http\Controllers\UserController::class, 'updateUser'])->name('user.update');
    Route::get('/users/get', [\App\Http\Controllers\UserController::class, 'get'] )->name('user.get');
    Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'store'])->name('user.store');
    Route::post('/users/change-password', [\App\Http\Controllers\UserController::class, 'changePassword'])->name('user.change-password');
    Route::get('/users/logout', [\App\Http\Controllers\UserController::class, 'logout'] )->name('logout');

    Route::post('/booking/create', [\App\Http\Controllers\BookingStaffController::class, 'create'])->name('booking.create');
    Route::get('/booking/staff/active', [\App\Http\Controllers\BookingStaffController::class, 'getAllByActive'])->name('booking.staff.active');
    Route::get('/booking/staff/history', [\App\Http\Controllers\BookingStaffController::class, 'getAllByHistory'])->name('booking.staff.history');
    Route::get('/booking/staff/{idBooking}', [\App\Http\Controllers\BookingStaffController::class, 'getById'])->name('booking.staff.detail');
    Route::put('/booking/staff/update/{idBooking}', [\App\Http\Controllers\BookingStaffController::class, 'update'])->name('booking.staff.update');

    Route::get('/booking/driver/active', [\App\Http\Controllers\BookingDriverController::class, 'getAllByActive'])->name('booking.driver.active');
    Route::get('/booking/driver/history', [\App\Http\Controllers\BookingDriverController::class, 'getAllByHistory'])->name('booking.driver.history');
    Route::get('/booking/driver/{idBooking}', [\App\Http\Controllers\BookingDriverController::class, 'getById'])->name('booking.driver.detail');
    Route::post('/booking/driver/update/{idBooking}', [\App\Http\Controllers\BookingDriverController::class, 'update'])->name('booking.driver.update');

    Route::get('/booking/admin/all', [\App\Http\Controllers\BookingAdminController::class, 'getAll'])->name('booking.admin.all');
    Route::get('/booking/admin/detail/{idBooking}', [\App\Http\Controllers\BookingAdminController::class, 'getById'])->name('booking.admin.detail');
    Route::put('/booking/admin/update/{idBooking}', [\App\Http\Controllers\BookingAdminController::class, 'update'])->name('booking.admin.update');
    Route::get('/admin/summary/dashboard', [\App\Http\Controllers\DashboardAdminController::class, 'getSummaryBooking'])->name('summary.admin.dashboard');
    Route::get('/admin/booking-waiting/dashboard', [\App\Http\Controllers\DashboardAdminController::class, 'getBookingWaiting'])->name('booking-waiting.admin.dashboard');

    Route::get('/admin/staff', [\App\Http\Controllers\StaffAdminController::class, 'getStaff'])->name('admin.staff');
    Route::post('/admin/update/user/{idUser}', [\App\Http\Controllers\UserController::class, 'updateUserAdmin'])->name('admin.user.update');
    Route::delete('/admin/delete/user/{idUser}', [\App\Http\Controllers\UserController::class, 'deleteUser'])->name('admin.user.delete');
    Route::get('/admin/driver', [\App\Http\Controllers\DriverAdminController::class, 'getDriver'])->name('admin.driver');
    Route::get('/admin/jadwal-driver', [\App\Http\Controllers\DriverAdminController::class, 'getJadwalDriver'])->name('admin.jadwal.driver');
    Route::get('/admin/driver-ready', [\App\Http\Controllers\DriverAdminController::class, 'getDriverReady'])->name('admin.driver.ready');
    Route::get('/admin/master', [\App\Http\Controllers\MasterAdminController::class, 'getMasterAdmin'])->name('admin.master');

    Route::get('/notif/staff', [\App\Http\Controllers\NotificationController::class, 'getNotifStaff'])->name('notif.staff');
    Route::get('/notif/driver', [\App\Http\Controllers\NotificationController::class, 'getNotifDriver'])->name('notif.driver');
    Route::get('/notif/admin', [\App\Http\Controllers\NotificationController::class, 'getNotifAdmin'])->name('notif.admin');

    Route::get('/jadwal-driver', [\App\Http\Controllers\JadwalDriverController::class, 'getDriver'])->name('jadwal.driver');
    Route::get('/city', [\App\Http\Controllers\CommonController::class, 'getCIty'])->name('city');


});

Route::get('/not/token',  function (Request $request) {
    return response()->json([
        'status' => false,
        'message' => 'Unauthenticated'
    ], 401);
})->name('not.token');
