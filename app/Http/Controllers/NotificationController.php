<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\HistoryStatusBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getNotifStaff ()
    {
        $user = Auth::user();
        $notif = Booking::with([
                'pickup_city',
                'destination_city',
                'history.status_history',
                'history' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                ])
                ->where('staff_id', $user->id)
                ->get();

        return response()->json([
            'data' => ['notif' => $notif],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function getNotifDriver ()
    {
        $user = Auth::user();
        $notif = Booking::with([
                'pickup_city',
                'destination_city',
                'history.status_history',
                'history' => function ($query) {
                            $query->orderBy('created_at', 'desc');
                        },
                ])
                ->where('driver_id', $user->id)
                ->get();

        return response()->json([
            'data' => ['notif' => $notif],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function getNotifAdmin ()
    {
        $notif = Booking::with([
                'pickup_city',
                'destination_city',
                'history.status_history',
                'history' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                ])
                ->get();

        return response()->json([
            'data' => ['notif' => $notif],
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
