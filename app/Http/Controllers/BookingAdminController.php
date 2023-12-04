<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\HistoryStatusBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingAdminController extends Controller
{
    public function getAll(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 5);
        $statusInput = $request->input('status');

        $status = '';
        switch ($statusInput) {
            case 'all':
                $status = null;
                break;
            case 'menunggu':
                $status = 1;
                break;
            case 'disetujui':
                $status = 2;
                break;
            case 'berjalan':
                $status = 3;
                break;
            case 'selesai':
                $status = 4;
                break;
            case 'ditolak':
                $status = 5;
                break;
            default:
                $status = 6;
        }

        $booking = Booking::with('user', 'driver', 'pickup_city', 'destination_city', 'status_booking', 'history.status_history')
                        ->orderBy('created_at', 'desc');
                        if ($status !== null) {
                            $booking->where('status_id', $status);
                        }

        $booking = $booking->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('status_id', 'like', '%' . $name . '%');
                });
            }
        });

        $booking = $booking->paginate(perPage: $size, page: $page);

        return response()->json([
            'data' => ['booking' => $booking],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total' => $booking->total(),
                'page' => $booking->currentPage(),
                'last_page' => $booking->lastPage()
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

            User::where('id', $request->driver_id)->update(['is_ready' => 1]);
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
