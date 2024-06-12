<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function updateProfilePhoto(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Store the uploaded file and get its path
        $path = $request->file('profile_photo')->store('profile_photos', 'public');

        // Delete the old profile photo if it exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Update user's profile photo path
        $user->profile_photo = $path;
        $user->save();

        // Return success response
        return response()->json([
            'message' => 'Profile photo updated successfully',
            'data' => $user
        ]);
    }
}
