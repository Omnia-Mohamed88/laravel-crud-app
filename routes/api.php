<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UploadController;

Route::get("/aa", function () {
    return response()->json(\App\Models\Category::all());
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [AuthController::class, 'reset']);
Route::post('new-password/email', [AuthController::class, 'sendResetLinkEmailNew']);
Route::post('new-password/reset', [AuthController::class, 'resetNew']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::post('password/verify-token', [AuthController::class, 'verifyResetToken']);

Route::middleware('auth:api')->group(function () {
    Route::get("/profile", [UserController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware(['role:admin|superadmin'])->group(function () {
        Route::post('categories/import', [CategoryController::class, 'import']);
        Route::post('/attachments', [UploadController::class, 'saveOnDisk']);
        Route::post('/delete-image', [UploadController::class, 'deleteImage']);
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    });

    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'addPermission']);
        Route::delete('roles/{role}/permissions', [RoleController::class, 'removePermission']);
        Route::apiResource('permissions', PermissionController::class);
        Route::post('role-permissions/assign', [RolePermissionController::class, 'assignPermissions']);
        Route::post('role-permissions/revoke', [RolePermissionController::class, 'revokePermissions']);
    });
});
