<?php

use App\Http\Controllers\Api\V1\BundleController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::get('/collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');
    Route::get('/collections/{slug}/products', [CollectionController::class, 'products']);

    Route::get('/bundles', [BundleController::class, 'index']);
    Route::get('/bundles/{slug}', [BundleController::class, 'show']);

    Route::get('/storefront', [StorefrontController::class, 'settings']);
    Route::get('/menus/{location}', [StorefrontController::class, 'menu']);
    Route::get('/pages/{slug}', [StorefrontController::class, 'page']);

    Route::post('/cart/validate', [CartController::class, 'validate']);
    Route::post('/cart/shipping', [CartController::class, 'shipping']);
    Route::post('/orders', [OrderController::class, 'store']);
});
