<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\HistoryStatusBooking;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingAdminController extends Controller
{
    public function getAll()
    {
        $booking = Booking::with('user', 'driver', 'pickup_city', 'destination_city', 'status_booking', 'history.status_history')->get();
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

    public function getById(int $id)
    {
        $booking = Booking::with('user', 'driver', 'pickup_city', 'destination_city', 'status_booking', 'history.status_history')->where('id', $id)->first();
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
        $booking = Booking::where('id', $id)->first();
        if (!$booking) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        if($request->status_id !== 0)
        {

            Booking::where('id', $id)
                ->update([
                    'status_id' => $request->status_id,
                    'driver_id' => $request->driver_id
                ]);

            HistoryStatusBooking::create([
                'status_history_id' => 2,
                'booking_id' => $booking->id,
                'description' => '',
                'location' => '',
                'image' => '',
                'is_read' => 0,
                'date_time' => Carbon::now()
            ]);
        }else{

            Booking::where('id', $id)->update(['status_id' => 5]);
            HistoryStatusBooking::create([
                'status_history_id' => 9,
                'booking_id' => $booking->id,
                'description' => $request->note,
                'location' => '',
                'image' => '',
                'is_read' => 0,
                'date_time' => Carbon::now()
            ]);
        }

        $updatedBooking = Booking::find($id);
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
