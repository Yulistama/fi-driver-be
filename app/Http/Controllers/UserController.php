<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if(User::where('email', $data['email'])->count() == 1){
            //
            throw new HttpResponseException(response([
                "errors" => [
                    "email" => [
                        "email already registered"
                    ]
                ]
                    ], 400));
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json([
            'data' => ['user' => $user],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->with('role', 'gender')->first();
        if(!$user || !Hash::check($data['password'], $user->password))
        {
            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => "email or password wrong"
                ]]
            ], 400));
        }

        $token = $user->createToken('user login')->plainTextToken;
        return response()->json([
            'data' => ['user' => $user, 'access_token' => $token],
            'status' => 'success',
        ], 200);

    }

    public function get(Request $request)
    {

        $user = Auth::user()->load('role', 'gender');
        return response()->json([
            'data' => ['user' => $user],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }
        if (isset($data['role_id'])) {
            $user->role_id = $data['role_id'];
        }
        if (isset($data['gender_id'])) {
            $user->gender_id = $data['gender_id'];
        }
        if (isset($data['is_status'])) {
            $user->is_status = $data['is_status'];
        }
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'data' => ['user' => $user],
            'status' => 'success',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);
    }

    public function logout(Request $request)
    {

        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout successful'], 200);
        }

        return response()->json(['message' => 'No user authenticated'], 401);

    }
}
