<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [AuthController::class, 'reset']);

// Public GET routes for categories and products
Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);

// Apply middleware to ensure authentication
Route::middleware('auth:api')->group(function () {
    // Category and Product CRUD routes (accessible by admin and superadmin)
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index']);
        Route::post('categories/import', [CategoryController::class, 'import']);
        Route::apiResource('products', ProductController::class)->except(['index']);
    });

    // User CRUD routes (only accessible by superadmin)
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/roles', [UserController::class, 'assignRoleToUser']);
    });

    // Role routes (accessible by superadmin)
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'addPermission']);
        Route::delete('roles/{role}/permissions', [RoleController::class, 'removePermission']);
    });

    // Permission routes (accessible by superadmin)
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('permissions', PermissionController::class);
    });

    // Role-Permission Management (accessible by superadmin)
    Route::middleware('role:superadmin')->group(function () {
        Route::post('role-permissions/assign', [RolePermissionController::class, 'assignPermissions']);
        Route::post('role-permissions/revoke', [RolePermissionController::class, 'revokePermissions']);
    });
});
