<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
