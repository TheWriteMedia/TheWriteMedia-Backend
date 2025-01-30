<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    Route::middleware(['cors'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    
        // Route::get('/user', function (Request $request) {
        //     return $request->user();
        // })->middleware('auth:sanctum');
    
        Route::middleware(['auth:sanctum', 'check.web.admin'])->group(function ()  {
            Route::get('/admin/dashboard', function () {
                return response()->json(['message' => 'Welcome Admin! Your middleware is working.']);
            });
        });
    
    
        Route::middleware(['auth:sanctum', 'check.author'])->group(function ()  {
            Route::get('/author/dashboard', function () {
                return response()->json(['message' => 'Welcome Author! Your middleware is working.']);
            });
        });

    });