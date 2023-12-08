<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DriverAdminController extends Controller
{
    public function getDriver(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 5);
        $statusInput = $request->input('status');

        $status = '';
        switch ($statusInput) {
            case 'aktif':
                $status = 1;
                break;
            case 'non-aktif':
                $status = 0;
                break;
            default:
                $status = null;
        }

        $driver = User::with('gender')
                ->where('role_id', 2)
                ->orderBy('created_at', 'desc')
                ->where(function ($query) use ($status) {
                    if ($status !== null) {
                        $query->orWhere('is_status', $status);
                    }
                });

        $driver = $driver->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('name', 'like', '%' . $name . '%');
                });
            }
        });

        $driver = $driver->paginate(perPage: $size, page: $page);

        $driver->getCollection()->transform(function ($item) {
            if ($item->image !== null) {
                $item->image = url('storage/' . $item->image);
            }
            return $item;
        });

        return response()->json([
            'data' => ['driver' => $driver],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total' => $driver->total(),
                'page' => $driver->currentPage(),
                'last_page' => $driver->lastPage()
            ]
        ], 200);
    }

    public function getJadwalDriver(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 5);
        $statusInput = $request->input('status');

        $status = '';
        switch ($statusInput) {
            case 'dipesan':
                $status = 1;
                break;
            case 'tersedia':
                $status = 0;
                break;
            default:
                $status = null;
        }

        $booking = Booking::where('estimated_pickup_time', $request->tgl)
                    ->where(function ($query) use ($request) {
                        $tgl = $request->input('tgl');
                        $query->orWhere('estimated_finish_time', $tgl);
                        $query->orWhere('driver_id', '!=', null);
                    })
                    ->get();

        $driver = User::with('gender')
                ->where('role_id', 2)
                ->orderBy('created_at', 'desc')
                ->where(function ($query) {
                    $query->orWhere('is_status', 1);
                })
                ->where(function ($query) use ($status) {
                    if ($status !== null) {
                        $query->orWhere('is_ready', $status);
                    }
                })
                ->where(function ($query) use ($booking) {
                    if ($booking !== null) {
                        $query->orWhere('id', '!=', $booking->driver_id);
                    }
                });

        $driver = $driver->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('name', 'like', '%' . $name . '%');
                });
            }
        });

        $driver = $driver->paginate(perPage: $size, page: $page);

        $driver->getCollection()->transform(function ($item) {
            if ($item->image !== null) {
                $item->image = url('storage/' . $item->image);
            }
            return $item;
        });

        return response()->json([
            'data' => ['driver' => $driver],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total' => $driver->total(),
                'page' => $driver->currentPage(),
                'last_page' => $driver->lastPage()
            ]
        ], 200);
    }

    public function getDriverReady(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 30);

        if(Booking::count() === 0){
            $ready = User::where('role_id', 2)->get();
        }else{

            $startDate = Carbon::parse($request->tgl_pickup)->startOfDay();
            $endDate = Carbon::parse($request->tgl_finish)->endOfDay();

            $notReady = [];
            // Query to get bookings with partial overlap within the date range
            $booked = Booking::where(function ($query) use ($startDate, $endDate) {
                    $query->where('driver_id', '!=', null)
                        ->where('status_id', '!=', 4) // <-- Exclude records with null driver_id
                        ->where('estimated_pickup_time', '>=', $startDate)
                        ->where('estimated_pickup_time', '<=', $endDate);
                })->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->where('driver_id', '!=', null) // <-- Exclude records with null driver_id
                        ->where('status_id', '!=', 4)
                        ->where('estimated_finish_time', '>=', $startDate)
                        ->where('estimated_finish_time', '<=', $endDate);
                })->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->where('driver_id', '!=', null) // <-- Exclude records with null driver_id
                        ->where('status_id', '!=', 4)
                        ->where('estimated_pickup_time', '<', $startDate)
                        ->where('estimated_finish_time', '>', $endDate);
                })->get();


            foreach($booked as $b){
                array_push($notReady, $b->driver_id);
            }

            $ready = User::whereNotIn('id', $notReady)
                    ->where('role_id', 2);
        }

        $driver = $ready->paginate(perPage: $size, page: $page);
        $driver->getCollection()->transform(function ($item) {
            if ($item->image !== null) {
                $item->image = url('storage/' . $item->image);
            }
            return $item;
        });

        return response()->json([
            'data' => ['driver' => $driver],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total' => $driver->total(),
                'page' => $driver->currentPage(),
                'last_page' => $driver->lastPage()
            ]
        ], 200);
    }
}
