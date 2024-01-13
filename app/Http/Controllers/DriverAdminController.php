<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
            $name = $request->input('search');
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
        $dateInput = $request->input('tgl');

        $date = '';
        if($dateInput){
            $date = $request->tgl;
        }else{
            $date = Carbon::today()->toDateString();
        }

        if(Booking::count() === 0){
            $allUsers = User::where('role_id', 2)
                        ->where('is_status', 1)
                        ->get();

            $ready = $allUsers->map(function ($user) {
                $user->ready = true;
                return $user;
            });
        }else{

            $notReady = [];
            $booked = Booking::where(function ($query) use ($date) {
                $query->where(function ($query) use ($date) {
                    $query->where('estimated_pickup_time', '<=', $date)
                        ->where('estimated_finish_time', '>=', $date)
                        ->where('status_id', '!=', 4)
                        ->where('driver_id', '!=', null);
                })
                ->orWhere(function ($query) use ($date) {
                    $query->whereDate('estimated_pickup_time', '=', $date)
                        ->where('status_id', '!=', 4)
                        ->where('driver_id', '!=', null);
                })
                ->orWhere(function ($query) use ($date) {
                    $query->whereDate('estimated_finish_time', '=', $date)
                        ->where('status_id', '!=', 4)
                        ->where('driver_id', '!=', null);
                });
            })
            ->get();

            // return $booked;

            foreach ($booked as $booking) {
                $notReady[] = $booking->driver_id;
            }

            // Query to get all users driver
            $allUsers = User::with('gender')
                        ->where('role_id', 2)
                        ->where('is_status', 1)
                        ->get();

            // Add 'ready' attribute to each user
            $ready = $allUsers->map(function ($user) use ($notReady, $booked) {
                $user->ready = !in_array($user->id, $notReady);
                $user->bookingId = $booked->where('driver_id', $user->id)->pluck('id')->first() ?? 0;
                return $user;
            });

        }

        $ready = $ready->filter(function ($user) use ($request) {
            $name = $request->input('name');
            if ($name) {
                return stripos($user->name, $name) !== false;
            }
            return true; // If no name filter, include all users
        });

        // Assuming $perPage and $page are already defined
        $perPage = request('size', 5);
        $page = request('page', 1);

        // $ready is your filtered and ready collection
        $total = count($ready);

        // Get the items for the current page
        $currentPageItems = array_slice($ready->toArray(), ($page - 1) * $perPage, $perPage);

        // Create the paginator instance
        $paginator = new LengthAwarePaginator(
            $currentPageItems,
            $total,
            $perPage,
            $page,
            ['path' => url()->current()]
        );

        // Transform the paginator to an array
        $paginator->getCollection()->transform(function ($item) {
            if ($item['image'] !== null) {
                $item['image'] = url('storage/' . $item['image']);
            }
            if ($item['attachment'] !== null) {
                $item['attachment'] = url('storage/' . $item['attachment']);
            }
            return $item;
        });

        // Transform the paginator to an array
        $paginatedData = $paginator->toArray();

        return response()->json([
            'data' => ['driver' => $paginatedData],
            'status' => 'success',
            'meta' => [
                'http_status' => 200,
                'total' => $total,
                'page' => $page,
                'last_page' => $paginator->lastPage(),
            ],
        ], 200);
    }

    public function getDriverReady(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 30);

        if(Booking::count() === 0){
            $ready = User::where('role_id', 2)
                    ->where('is_ready', 0)
                    ->get();
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
                    ->where('is_ready', 0)
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
