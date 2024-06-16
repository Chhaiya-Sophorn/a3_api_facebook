<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;


class UserController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message'       => 'Login success',
            'data'  => $request->user(),
        ]);
    }
    public function register(Request $request): JsonResponse
    {
        return response()([
            'message' => 'Register success',
            'data' => $request->user()
        ]);
    }
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout success'
        ]);
    }

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $user = new UserResource($user);
        return response(['success' => true, 'data' => $user, 'msg' => 'get user successfully']);
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        return response()->json([
            'data' => $user
        ]);
    }
    
}
