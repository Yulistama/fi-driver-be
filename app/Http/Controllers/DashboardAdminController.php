<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function getSummaryBooking()
    {
        $booking_wait = Booking::where('status_id', 1)->get()->count();
        $booking_approved = Booking::where('status_id', 2)->get()->count();
        $booking_berjalan = Booking::where('status_id', 3)->get()->count();
        $booking_done = Booking::where('status_id', 4)->get()->count();
        $booking_reject = Booking::where('status_id', 5)->get()->count();
        $booking_cancel = Booking::where('status_id', 6)->get()->count();
        $booking_total = Booking::get()->count();

        $total_staff = User::where('role_id', 3)->get()->count();
        $total_driver = User::where('role_id', 2)->get()->count();

        $booking = Booking::with('user', 'driver', 'pickup_city', 'destination_city', 'status_booking')
                        ->orderBy('created_at', 'desc')
                        ->where('status_id', 1)
                        ->get();

        return response()->json([
            'data' => ['summary' => [
                'booking_menunggu' => $booking_wait,
                'booking_disetujui' => $booking_approved,
                'booking_berjalan' => $booking_berjalan,
                'booking_selesai' => $booking_done,
                'booking_ditolak' => $booking_reject,
                'booking_batal' => $booking_cancel,
                'booking_total' => $booking_total,
                'total_staff' => $total_staff,
                'total_driver' => $total_driver,
                'booking' => $booking,
            ]],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function getBookingWaiting(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 5);

        $booking = Booking::with('user', 'driver', 'pickup_city', 'destination_city', 'status_booking')
                        ->orderBy('created_at', 'desc')
                        ->where('status_id', 1);

        $booking = $booking->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('name', 'like', '%' . $name . '%');
                });
            }
        });

        $booking = $booking->paginate(perPage: $size, page: $page);

        return response()->json([
            'data' => ['booking' => $booking],
            'status' => 'success',
            'meta' => [
                'http_status' => 200,
                'total' => $booking->total(),      // Get total number of items
                'page' => $booking->currentPage(), // Get the current page
                'last_page' => $booking->lastPage() // Get the last page number
            ]
        ], 200);
    }
}
