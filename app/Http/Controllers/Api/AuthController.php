<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Validator;

class AuthController extends Controller
{

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'nickname' => 'required|string|max:30',
            'business_name' => 'required|string|max:100',
            'email' => 'required|string|max:150|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => 'required|string|max:255',
        ]);

        if($validator->fails()) {
            return response([
                'message' => trans('validation.request_error'),
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $business = Business::create([
            'name' => $request->business_name,
        ]);

        $user = User::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken($request->device_name);

        return response()->json([
            'message' => 'User Registered',
            'data' => [
                'token' => $token->plainTextToken,
                'user' => $user,
            ],
        ]);

    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if($validator->fails()) {
            return response([
                'message' => trans('validation.request_error'),
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => trans('auth.failed'),
            ], 401);
        }

        $token = $user->createToken($request->device_name);

        return response()->json([
            'message' => trans('auth.success'),
            'data' => [
                'token' => $token->plainTextToken,
                'user' => $user,
            ],
        ]);

    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
           'message' => trans('auth.logout')
        ]);
    }

}
