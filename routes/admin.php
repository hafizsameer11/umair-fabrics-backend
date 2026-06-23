<?php

use App\Http\Controllers\Admin\BundleController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShippingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    Route::resource('collections', CollectionController::class)->except(['show']);

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('homepage', [HomepageSectionController::class, 'index'])->name('homepage.index');
    Route::post('homepage', [HomepageSectionController::class, 'store'])->name('homepage.store');
    Route::put('homepage/{section}', [HomepageSectionController::class, 'update'])->name('homepage.update');
    Route::delete('homepage/{section}', [HomepageSectionController::class, 'destroy'])->name('homepage.destroy');

    Route::resource('bundles', BundleController::class)->except(['show']);

    Route::get('hero-slides', [HeroSlideController::class, 'index'])->name('hero-slides.index');
    Route::post('hero-slides', [HeroSlideController::class, 'store'])->name('hero-slides.store');
    Route::put('hero-slides/{slide}', [HeroSlideController::class, 'update'])->name('hero-slides.update');
    Route::delete('hero-slides/{slide}', [HeroSlideController::class, 'destroy'])->name('hero-slides.destroy');

    Route::resource('pages', PageController::class)->except(['show']);

    Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('menus/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
    Route::delete('menus/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

    Route::get('shipping', [ShippingController::class, 'edit'])->name('shipping.edit');
    Route::put('shipping', [ShippingController::class, 'update'])->name('shipping.update');
});
