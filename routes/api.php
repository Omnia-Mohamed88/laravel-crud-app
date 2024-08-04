<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;


// API Routes
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
