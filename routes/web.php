<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorEventController;
use App\Http\Controllers\Vendor\VendorListController;
use App\Http\Controllers\Vendor\VendorProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:vendor')->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [VendorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [VendorProfileController::class, 'update'])->name('profile.update');
        Route::get('/events/create', [VendorEventController::class, 'create'])->name('events.create');
        Route::post('/events', [VendorEventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}/edit', [VendorEventController::class, 'edit'])->name('events.edit');
    });
});

Route::get('/vendors', [VendorListController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{slug}', [\App\Http\Controllers\Vendor\VendorPublicController::class, 'show'])->name('vendors.show');
Route::get('/e/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/e/{slug}/ics', [\App\Http\Controllers\EventCalendarController::class, 'ics'])->name('events.ics');
Route::post('/e/{slug}/reserve', [\App\Http\Controllers\OrderController::class, 'reserve'])->middleware('auth')->name('orders.reserve');
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get('/events', [\App\Http\Controllers\SearchController::class, 'index'])->name('events.index');
Route::get('/contact', fn() => view('contact'))->name('contact');

Route::middleware('auth')->group(function () {
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    });

    Route::post('/events/{slug}/save', [\App\Http\Controllers\SaveEventController::class, 'store'])->name('events.save');
    Route::delete('/events/{slug}/save', [\App\Http\Controllers\SaveEventController::class, 'destroy'])->name('events.unsave');
    Route::post('/vendors/{slug}/follow', [\App\Http\Controllers\FollowVendorController::class, 'store'])->name('vendors.follow');
    Route::delete('/vendors/{slug}/follow', [\App\Http\Controllers\FollowVendorController::class, 'destroy'])->name('vendors.unfollow');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/saved', [\App\Http\Controllers\Account\AccountSavedController::class, 'index'])->name('saved');
    });
});

require __DIR__.'/auth.php';
