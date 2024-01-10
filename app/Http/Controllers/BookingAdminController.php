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
        $date = $request->input('date');
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
                        ->orderBy('created_at', 'desc')
                        ->when($date, function ($query) use ($date) {
                            return $query->whereDate('created_at', $date);
                        });
                        if ($status !== null) {
                            $booking->where('status_id', $status);
                        }

        // if ($booking->user && $booking->user->image !== null) {
        //     $booking->user->image = url('storage/' . $booking->user->image);
        // }
        // if ($booking->driver && $booking->driver->image !== null) {
        //     $booking->driver->image = url('storage/' . $booking->driver->image);
        // }



        $booking = $booking->where(function (Builder $builder) use ($request) {
            $name = $request->input('search');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('code', 'like', '%' . $name . '%')
                            ->orWhereHas('user', function ($query) use ($name) {
                                $query->where('name', 'like', '%' . $name . '%');
                            })
                            ->orWhereHas('driver', function ($query) use ($name) {
                                $query->where('name', 'like', '%' . $name . '%');
                            })
                            ->orWhereHas('status_booking', function ($query) use ($name) {
                                $query->where('name', 'like', '%' . $name . '%');
                            });
                });
            }
        });

        // $booking->transform(function ($item) {
        //     if ($item->user && $item->user->image !== null) {
        //         $item->user->image = url('storage/' . $item->user->image);
        //     }
        //     if ($item->driver && $item->driver->image !== null) {
        //         $item->driver->image = url('storage/' . $item->driver->image);
        //     }

        //     return $item;
        // });
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
                    ])->where('id', $id)->first();
            if ($booking && $booking->history) {
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

            // User::where('id', $request->driver_id)->update(['is_ready' => 1]);
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
