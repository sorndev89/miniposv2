<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController; // Corrected namespace
use App\Http\Controllers\Api\UnitController; // Import UnitController
use App\Http\Controllers\Api\UserController; // Import UserController
use App\Http\Controllers\Api\OrderController; // Assuming it will be under Http/Controllers directly
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController; // Corrected to Api namespace
use App\Http\Controllers\Api\DashboardController; // Import DashboardController
use App\Http\Controllers\StockController;
use App\Http\Controllers\Api\StockImportController;
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

// Authentication Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (requires authentication using JWT)
Route::middleware('auth:api')->group(function () {
    // User related routes (e.g., logout, get authenticated user)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']); // Added token refresh
    Route::get('/user', [AuthController::class, 'user']); // Get authenticated user

    // Category Management
    Route::apiResource('categories', CategoryController::class);

    // Unit Management
    Route::apiResource('units', UnitController::class);

    // User Management
    Route::apiResource('users', UserController::class);

    // Product Management
    Route::apiResource('products', ProductController::class);

    // Stock Import Management
    Route::apiResource('stock-imports', StockImportController::class);

    // Stock Management
    Route::post('/stock/import', [StockController::class, 'import']);

    // POS - Order creation
    Route::post('/orders', [OrderController::class, 'store']);

    // Reports & History
    Route::get('/reports/sales', [ReportController::class, 'getSalesReports']);
    Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPdf']);

    // Dashboard API Routes
    Route::get('/dashboard/summary', [DashboardController::class, 'getSummary']);
    Route::get('/dashboard/activities', [DashboardController::class, 'getActivities']);
});