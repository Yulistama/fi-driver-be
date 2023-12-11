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
use Illuminate\Support\Str;

class BookingStaffController extends Controller
{
    public function create(BookingCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $booking = new Booking($data);
        $booking->code = str_pad(rand(0, 99999), 8, '0', STR_PAD_LEFT);
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
            ->orderBy('created_at', 'desc')
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

    public function update(int $id, Request $request)
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
            ->update(['status_id' => $request->status_id]);

        $updatedBooking = Booking::find($id);

        HistoryStatusBooking::create([
            'status_history_id' => $request->status_history_id,
            'booking_id' => $booking->id,
            'description' => $request->description,
            'location' => $request->location,
            'image' => $request->image,
            'is_read' => 0,
            'date_time' => Carbon::now()
        ]);

        return response($updatedBooking)->setStatusCode(200);
    }
}
