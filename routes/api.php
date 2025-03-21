<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes with rate limiting
Route::middleware(['auth:sanctum', 'throttle:4,1'])->group(function () {
    Route::get('/', function() {
        return response()->json([
            'message' => 'API is working'
        ]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::post('/bulk', [AttendanceController::class, 'markBulkAttendance']);
        Route::post('/{student}', [AttendanceController::class, 'markAttendance']);
        Route::get('/report/{student}', [AttendanceController::class, 'getReport']);
        Route::get('/class/{class}', [AttendanceController::class, 'getClassReport']);
    });
});
