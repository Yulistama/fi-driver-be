<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalDriverController extends Controller
{
    public function getDriver ()
    {
        $user = Auth::user()->where('role_id', 2)->get();

        return response()->json([
            'data' => ['driver' => $user],
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
