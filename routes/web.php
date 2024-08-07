<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('password/reset/{token}', function ($token) {
    return view('reset')->with('token', $token);
})->name('password.reset');

Route::post('password/reset', [AuthController::class, 'reset']);