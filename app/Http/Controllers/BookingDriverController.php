<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\HistoryStatusBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookingDriverController extends Controller
{
    public function getAllByActive(): JsonResponse
    {
        $user = Auth::user();
        $bookings = Booking::with('user', 'driver', 'pickup_city', 'status_booking', 'destination_city')
            ->where('driver_id', $user->id)
            ->where(function ($query) {
                $query->where('status_id', 1)
                    ->orWhere('status_id', 2)
                    ->orWhere('status_id', 3);
                })
            ->orderBy('created_at', 'desc')
            ->get();
        // foreach ($bookings as $booking) {
        //     if ($booking->user && $booking->user->image !== null) {
        //         $booking->user->image = url('storage/' . $booking->user->image);
        //     }

        //     if ($booking->driver && $booking->driver->image !== null) {
        //         $booking->driver->image = url('storage/' . $booking->driver->image);
        //     }
        // }
        foreach ($bookings as $booking) {
            if ($booking->user && $booking->user->image !== null) {
                // Check if the image URL already starts with "http://" or "https://"
                if (!preg_match("~^(?:f|ht)tps?://~i", $booking->user->image)) {
                    $booking->user->image = url('storage/' . $booking->user->image);
                }
            }

            if ($booking->driver && $booking->driver->image !== null) {
                // Check if the image URL already starts with "http://" or "https://"
                if (!preg_match("~^(?:f|ht)tps?://~i", $booking->driver->image)) {
                    $booking->driver->image = url('storage/' . $booking->driver->image);
                }
            }
        }

        return response()->json([
            'data' => ['booking' => $bookings],
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
        $bookings = Booking::with('user', 'driver', 'pickup_city', 'status_booking', 'destination_city')
            ->where('driver_id', $user->id)
            ->where(function ($query) {
                $query->where('status_id', 4)
                    ->orWhere('status_id', 5)
                    ->orWhere('status_id', 6);
                })
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($bookings as $booking) {
            if ($booking->user && $booking->user->image !== null) {
                // Check if the image URL is already a full URL
                if (!filter_var($booking->user->image, FILTER_VALIDATE_URL)) {
                    $booking->user->image = url('storage/' . $booking->user->image);
                }
            }

            if ($booking->driver && $booking->driver->image !== null) {
                // Check if the image URL is already a full URL
                if (!filter_var($booking->driver->image, FILTER_VALIDATE_URL)) {
                    $booking->driver->image = url('storage/' . $booking->driver->image);
                }
            }
        }


        return response()->json([
            'data' => ['booking' => $bookings],
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
        $booking = Booking::with([
                'user',
                'driver',
                'pickup_city',
                'destination_city',
                'status_booking',
                'history.status_history',
                // 'history' => function ($query) {
                //     $query->orderBy('created_at', 'desc');
                // },
            ])->where('id', $id)
            ->where('driver_id', $user->id)
            ->first();

        if ($booking && $booking->history) {
            // If there are multiple history records, loop through them
            foreach ($booking->history as $history) {
                if ($history->image) {
                    $history->image = url('storage/' . $history->image);
                }
            }
        }
        if ($booking->user && $booking->user->image !== null) {
            $booking->user->image = url('storage/' . $booking->user->image);
        }
        if ($booking->driver && $booking->driver->image !== null) {
            $booking->driver->image = url('storage/' . $booking->driver->image);
        }

        if ($booking === null) {
            return response()->json(['message' => 'Booking not found'])->setStatusCode(404);
        }

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

    public function getByIdHistory(int $id): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::with([
                'user',
                'driver',
                'pickup_city',
                'destination_city',
                'status_booking',
                'history.status_history',
                'history' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
            ])->where('id', $id)
            ->where('driver_id', $user->id)
            ->first();
        if ($booking && $booking->history) {
            foreach ($booking->history as $history) {
                if ($history->image) {
                    $history->image = url('storage/' . $history->image);
                }
            }
        }

        if ($booking === null) {
            return response()->json(['message' => 'Booking not found'])->setStatusCode(404);
        }

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

        $booking = Booking::where('id', $id)->where('driver_id', $user->id)->first();
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
            ->where('driver_id', $user->id)
            ->update(['status_id' => $request->status_id]);

        $updatedBooking = Booking::find($id);


        if($request->hasFile('image')){
            $file = $request->file('image')->store('image', 'public');
        }

        HistoryStatusBooking::create([
            'status_history_id' => $request->status_history_id,
            'booking_id' => $booking->id,
            'description' => $request->description,
            'location' => $request->location,
            'image' => $request->hasFile('image') ? $file : null,
            'is_read' => $request->is_read,
            'date_time' => Carbon::now()
        ]);

        if($request->status_id === 4){
            User::where('id', $user->id)->update(['is_ready' => 0]);
        }

        return response()->json([
            'data' => ['booking' => $updatedBooking],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }
}
