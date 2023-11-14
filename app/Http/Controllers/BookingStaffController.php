<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingCreateRequest;
use App\Http\Requests\BookingUpdateRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\HistoryStatusBooking;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingStaffController extends Controller
{
    public function create(BookingCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $booking = new Booking($data);
        $booking->status_id = 1;
        $booking->staff_id = $user->id;
        $booking->driver_id = null;
        $booking->save();

        HistoryStatusBooking::create([
            'status_history_id' => 1,
            'booking_id' => $booking->id,
            'description' => '',
            'location' => '',
            'image' => '',
            'is_read' => 0,
            'date_time' => Carbon::now()
        ]);

        return response()->json([
            'data' => ['booking' => new BookingResource($booking)],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function getAllByActive(): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::with('user', 'driver', 'pickup_city', 'status_booking', 'destination_city')
            ->where('staff_id', $user->id)
            ->where(function ($query) {
                $query->where('status_id', 1)
                    ->orWhere('status_id', 2)
                    ->orWhere('status_id', 3);
                })
            ->get();

        return response()->json([
            'data' => ['booking' => $booking],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);

    }

    public function getAllByHistory(): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::with('user', 'driver', 'pickup_city', 'status_booking', 'destination_city')
            ->where('staff_id', $user->id)
            ->where(function ($query) {
                $query->where('status_id', 4)
                    ->orWhere('status_id', 5)
                    ->orWhere('status_id', 6);
                })
            ->get();

        return response()->json([
            'data' => ['booking' => $booking],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function getById(int $id): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::with('user', 'driver', 'pickup_city', 'destination_city', 'status_booking', 'history.status_history')
            ->where('id', $id)
            ->where('staff_id', $user->id)
            ->first();

        return response()->json([
            'data' => ['booking' => $booking],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);

    }

    public function update(int $id)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $id)->where('staff_id', $user->id)->first();
        if (!$booking) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        Booking::where('id', $id)
            ->where('staff_id', $user->id)
            ->update(['status_id' => 6]);

        $updatedBooking = Booking::find($id);

        HistoryStatusBooking::create([
            'name' => 'Pesan Dibatalkan',
            'booking_id' => $booking->id,
            'description' => 'Staff membatalkan pesanan',
            'image' => 'image.png',
            'is_read' => 0,
            'date_time' => Carbon::now()
        ]);

        return response($updatedBooking)->setStatusCode(200);
    }
}
