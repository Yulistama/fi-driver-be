<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MasterAdminController extends Controller
{
    public function getMasterAdmin(Request $request)
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

        $master = User::with('gender')
                ->where('role_id', 1)
                ->orderBy('created_at', 'desc')
                ->where(function ($query) use ($status) {
                    if ($status !== null) {
                        $query->orWhere('is_status', $status);
                    }
                });

        $master = $master->where(function (Builder $builder) use ($request) {
            $name = $request->input('search');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('name', 'like', '%' . $name . '%');
                });
            }
        });

        $master = $master->paginate(perPage: $size, page: $page);

        $master->getCollection()->transform(function ($item) {
            if ($item->image !== null) {
                $item->image = url('storage/' . $item->image);
            }
            return $item;
        });

        return response()->json([
            'data' => ['master' => $master],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total' => $master->total(),
                'page' => $master->currentPage(),
                'last_page' => $master->lastPage()
            ]
        ], 200);
    }
}
