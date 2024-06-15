<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikesController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/user')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'index'])->middleware('auth:sanctum');
});

Route::prefix('/information')->group(function () {
    Route::get('{userId}/profile', [UserController::class,'show']);
    Route::put('{userId}/profile', [UserController::class,'update']);
    Route::post('{userId}/profile/picture', [AuthController::class,'uploadProfilePicture']);
});


Route::prefix('/post')->group(function () {
    Route::post('/create', [PostController::class, 'store']);
    Route::get('/get/{id}', [PostController::class, 'show']);
    Route::put('/update/{id}', [PostController::class, 'update']);
    Route::delete('/delete/{id}', [PostController::class, 'destroy']);
});

Route::prefix('/comment')->group(function() {
    Route::post('/create/{postId}', [CommentController::class, 'store']);
    Route::get('/get/{postId}', [CommentController::class, 'show']);
    Route::put('/update/{commentId}', [CommentController::class, 'update']);
    Route::delete('/delete/{commetId}', [CommentController::class, 'destroy']);
});

Route::post('/password/email', [AuthController::class, 'sendEmailVerify']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);
Route::post('/like-post',[LikesController::class, 'store']);
