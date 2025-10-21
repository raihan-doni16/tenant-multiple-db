<?php

use App\Http\Controllers\Central\AdminAuthController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\ProductController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

Route::prefix('admin')->group(function () {
    Route::post('auth/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::post('auth/logout', [AdminAuthController::class, 'logout']);
        Route::get('auth/me', [AdminAuthController::class, 'me']);

        Route::prefix('tenants')->group(function () {
            Route::get('/', [TenantController::class, 'index']);
            Route::post('/', [TenantController::class, 'store']);
            Route::delete('{tenantId}', [TenantController::class, 'destroy']);
        });
    });
});

Route::pattern('tenant', '[A-Za-z0-9_-]+');

Route::middleware([
    InitializeTenancyByPath::class,
])->prefix('{tenant}')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::post('products', [ProductController::class, 'store']);
        Route::patch('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart/items', [CartController::class, 'addItem']);
        Route::patch('cart/items/{shopping_cart_item}', [CartController::class, 'updateItem']);
        Route::delete('cart/items/{shopping_cart_item}', [CartController::class, 'removeItem']);
    });
});
