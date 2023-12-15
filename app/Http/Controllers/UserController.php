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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        // Check if the email is registered
        $existingUser = User::where('email', $data['email'])->first();
        if (!$existingUser) {
            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => "Email not registered"
                ]]
            ], 400));
        }

        $aktiveUser = User::where('email', $data['email'])->first();
        if ($aktiveUser->is_status === 0) {
            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => "User not active"
                ]]
            ], 400));
        }

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
        if($user->image !== null){
            $user->image = url('storage/'.$user->image);
        }
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'email_verified_at' => 'nullable',
            'password' => 'required',
            'role_id' => 'required',
            'phone' => 'required',
            'position' => 'nullable',
            // 'image' => 'nullable',
            'is_status' => 'required',
            'is_ready' => 'nullable',
            'number_vehicle' => 'nullable',
            'tranpostation_type' => 'nullable',
            'gender_id' => 'required',
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => [$validator->errors()->first()]
                ]]
            ], 400));
        }

        if($request->hasFile('image')){
            $file = $request->file('image')->store('image', 'public');
        }else{
            $file = null;
        }

        $user = new User($request->all());
        $user->image = $file;
        $user->password = Hash::make($request['password']);
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

    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required',
            'phone' => 'required',
            'position' => 'nullable',
            'is_status' => 'required',
            'number_vehicle' => 'nullable',
            'tranpostation_type' => 'nullable',
            'gender_id' => 'required',
        ]);

        if ($validator->fails()) {
        return response()->json(
            [
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        if($request->hasFile('image')){
            if(isset($user->image) && file_exists(storage_path('app/public/'. $user->image))){
                Storage::delete('public/'. $user->image);
            }
            $file = $request->file('image')->store('image', 'public');
            $user->image = $file;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role_id = $request->role_id;
        $user->gender_id = $request->gender_id;
        $user->is_status = $request->is_status;
        $user->number_vehicle = $request->number_vehicle;
        $user->tranpostation_type = $request->tranpostation_type;
        $user->position = $request->position;

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update([$user]);

        $user->image = url('storage/'.$user->image);

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

    public function updateUserAdmin(int $id, Request $request)
    {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required',
            'phone' => 'required',
            'position' => 'nullable',
            'is_status' => 'required',
            'number_vehicle' => 'nullable',
            'tranpostation_type' => 'nullable',
            'gender_id' => 'required',
        ]);

        if ($validator->fails()) {
        return response()->json(
            [
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        if($request->hasFile('image')){
            if(isset($user->image) && file_exists(storage_path('app/public/'. $user->image))){
                Storage::delete('public/'. $user->image);
            }
            $file = $request->file('image')->store('image', 'public');
            $user->image = $file;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->number_vehicle = $request->number_vehicle;
        $user->tranpostation_type = $request->tranpostation_type;
        $user->position = $request->position;
        $user->role_id = $request->role_id;
        $user->gender_id = $request->gender_id;
        $user->is_status = $request->is_status;

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update([$user]);

        $user->image = url('storage/'.$user->image);

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

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json(
            [
                'errors' => [
                    'message' => $validator->errors()
                ]
            ], 400);

            // throw new HttpResponseException(response([
            //     "errors" => [[
            //         "message" => $validator->errors()
            //     ]]
            // ], 400));
        }

        // Verify old password
        if (!Hash::check($request->old_password, $user->password)) {
            // return response()->json(
            // [
            //     'message' => 'errors',
            //     'errors' => 'Password lama salah'
            // ], 400);

            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => 'Password lama salah'
                ]]
            ], 400));
        }

        // Update password
        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'status' => 'success merubah password',
            'meta' => [
                'http_status'=> 200,
                'total'=> 0,
                'page'=> 0,
                'last_page'=> 0
            ]
        ], 200);

    }

    public function deleteUser(int $id)
    {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => 'User not found'
                ]]
            ], 400));
        }

        // Delete the user
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw new HttpResponseException(response([
                "errors" => [[
                    "message" => "User not found"
                ]]
            ], 400));
        }

        $user->name = $user->name;
        $user->email = $user->email;
        $user->phone = $user->phone;
        $user->number_vehicle = $user->number_vehicle;
        $user->tranpostation_type = $user->tranpostation_type;
        $user->position = $user->position;
        $user->role_id = $user->role_id;
        $user->gender_id = $user->gender_id;
        $user->is_status = $user->is_status;

        if (isset($request->new_password)) {
            $user->password = Hash::make($request->new_password);
        }

        $user->update([$user]);

        return response()->json([
            'data' => ['user' => $user],
            'status' => 'success change password',
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
