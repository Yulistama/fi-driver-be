<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StaffAdminController extends Controller
{
    public function getStaff(Request $request)
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

        $staff = User::with('gender')
                ->where('role_id', 3)
                ->orderBy('created_at', 'desc')
                ->where(function ($query) use ($status) {
                    if ($status !== null) {
                        $query->orWhere('is_status', $status);
                    }
                });

        $staff = $staff->where(function (Builder $builder) use ($request) {
            $name = $request->input('search');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('name', 'like', '%' . $name . '%');
                });
            }
        });

        $staff = $staff->paginate(perPage: $size, page: $page);

        $staff->getCollection()->transform(function ($item) {
            if ($item->image !== null) {
                $item->image = url('storage/' . $item->image);
            }
            return $item;
        });

        return response()->json([
            'data' => ['staff' => $staff],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total' => $staff->total(),
                'page' => $staff->currentPage(),
                'last_page' => $staff->lastPage()
            ]
        ], 200);
    }
}
