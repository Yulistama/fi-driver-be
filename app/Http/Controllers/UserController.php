<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
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

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();
        if(!$user || !Hash::check($data['password'], $user->password))
        {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "email or password wrong"
                    ]
                ]
            ], 401));
        }

        $token = $user->createToken('user login')->plainTextToken;
        return response()->json(['user' => $user, 'access_token' => $token]);

    }

    public function get(Request $request)
    {

        $user = Auth::user();
        return new UserResource($user);
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
