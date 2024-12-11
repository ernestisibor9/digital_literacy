<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Public Routes
// Public Routes
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User-only routes
    Route::middleware(['role:user'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'profile']);
        Route::put('/update-profile/{id}', [ProfileController::class, 'updateProfile']);
        Route::put('/update-password/{id}', [ProfileController::class, 'updatePassword']);
    });

    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('course', CourseController::class);
    });
});
