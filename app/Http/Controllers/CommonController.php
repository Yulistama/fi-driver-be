<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getCity()
    {
        $city = City::all();

        return response()->json([
            'data' => ['city' => $city],
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
