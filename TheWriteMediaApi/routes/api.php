<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\NewsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    Route::middleware(['cors'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

          //FORGOT PASSWORD
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
        Route::get('/user', function (Request $request) {
            return $request->user();    
        })->middleware('auth:sanctum');

        Route::middleware(['auth:sanctum'])->get('/user/profile', [AuthController::class, 'getProfile']);
        Route::middleware(['auth:sanctum'])->put('/user/profile', [AuthController::class, 'updateProfile']);

    

        //WEB ADMIN ROUTES
        Route::middleware(['auth:sanctum', 'check.web.admin'])->group(function ()  {
            Route::get('/admin/dashboard', function () {
                return response()->json(['message' => 'Welcome Admin! Your middleware is working.']);
            });

             //AUTHOR MANAGEMENT ROUTES
            Route::get('/admin/authors', action: [AuthorController::class, 'index']); // show all authors
            Route::post('/admin/authors', [AuthorController::class, 'store']); // Create a new author
            Route::get('/admin/authors/{author}', [AuthorController::class, 'show']); // Show a specific author 
            Route::put('/admin/authors/{author}', [AuthorController::class, 'update']); // Update an author 
            Route::delete('/admin/authors/{author}', [AuthorController::class, 'destroy']); // Delete an author 
            Route::patch('admin/authors/{author}/restore', [AuthorController::class, 'restore']); // Reactivate an author
        });
    
        
        Route::middleware(['auth:sanctum', 'check.author'])->group(function ()  {
            Route::get('/author/dashboard', function () {
                return response()->json(['message' => 'Welcome Author! Your middleware is working.']);
            });

             //BOOK MANAGEMENT ROUTES
             Route::get('/author/books', action: [BookController::class, 'index']); // show all news
             Route::post('/author/books', [BookController::class, 'store']); // Create a new news
             Route::get('/author/books/{book}', [BookController::class, 'show']); // Show a specific news 
             Route::put('/author/books/{book}', [BookController::class, 'update']); // Update an news 
             Route::delete('/author/books/{book}', [BookController::class, 'destroy']); // Delete an news 
             Route::patch('author/books/{book}/restore', [BookController::class, 'restore']); // Reactivate an news

            //NEWS MANAGEMENT ROUTES
            Route::get('/author/news', action: [NewsController::class, 'index']); // show all news
            Route::post('/author/news', [NewsController::class, 'store']); // Create a new news
            Route::get('/author/news/{news}', [NewsController::class, 'show']); // Show a specific news 
            Route::put('/author/news/{news}', [NewsController::class, 'update']); // Update an news 
            Route::delete('/author/news/{news}', [NewsController::class, 'destroy']); // Delete an news 
            Route::patch('author/news/{news}/restore', [NewsController::class, 'restore']); // Reactivate an news
        });

    });