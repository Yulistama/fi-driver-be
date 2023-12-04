<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
