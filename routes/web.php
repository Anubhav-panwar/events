<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EventReminderController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorEventController;
use App\Http\Controllers\Vendor\VendorListController;
use App\Http\Controllers\Vendor\VendorProfileController;
use App\Http\Controllers\Vendor\VendorTicketController;
use App\Http\Controllers\Vendor\CheckInController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user?->isVendor()) {
        return redirect()->route('vendor.dashboard');
    }
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
        Route::put('/events/{event}', [VendorEventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [VendorEventController::class, 'destroy'])->name('events.destroy');
        Route::patch('/events/{event}/publish', [VendorEventController::class, 'publish'])->name('events.publish');
        Route::patch('/events/{event}/unpublish', [VendorEventController::class, 'unpublish'])->name('events.unpublish');
        Route::get('/events/{event}/tickets/create', [VendorTicketController::class, 'create'])->name('tickets.create');
        Route::post('/events/{event}/tickets', [VendorTicketController::class, 'store'])->name('tickets.store');
        Route::get('/checkin', [CheckInController::class, 'form'])->name('checkin.form');
        Route::post('/checkin', [CheckInController::class, 'validateTicket'])->name('checkin.validate');
        Route::get('/orders', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'index'])->name('orders.index');
    });
});

Route::get('/vendors', [VendorListController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{slug}', [\App\Http\Controllers\Vendor\VendorPublicController::class, 'show'])->name('vendors.show');
Route::get('/e/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/e/{slug}/ics', [\App\Http\Controllers\EventCalendarController::class, 'ics'])->name('events.ics');
Route::get('/e/{slug}/share/{channel}', [ShareController::class, 'redirect'])->name('events.share');
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get('/events', [\App\Http\Controllers\SearchController::class, 'index'])->name('events.index');
Route::get('/contact', fn() => view('contact'))->name('contact');
Route::view('/about', 'pages.about')->name('about');
Route::view('/services', 'pages.services')->name('services');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/portfolio', 'pages.portfolio')->name('portfolio');
Route::view('/testimonials', 'pages.testimonials')->name('testimonials');
Route::view('/faq', 'pages.faq')->name('faq');

Route::get('/storage/{path}', function (string $path) {
    $cleanPath = ltrim(preg_replace('#^app/public/#', '', $path), '/');
    $baseDir = realpath(storage_path('app/public')) ?: storage_path('app/public');
    $publicDir = realpath(public_path()) ?: public_path();

    $candidates = [
        storage_path('app/public/' . $cleanPath),
        public_path('storage/' . $cleanPath),
        base_path('storage/app/public/' . $cleanPath),
        base_path('public/storage/' . $cleanPath),
    ];

    foreach ($candidates as $candidate) {
        $real = realpath($candidate);
        if ($real && is_file($real)) {
            $normReal = strtolower(str_replace('\\', '/', $real));
            $normBase = strtolower(str_replace('\\', '/', $baseDir));
            $normPublic = strtolower(str_replace('\\', '/', $publicDir));

            if (str_starts_with($normReal, $normBase) || str_starts_with($normReal, $normPublic)) {
                $mime = mime_content_type($real) ?: 'application/octet-stream';
                return response()->file($real, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000',
                ]);
            }
        }
    }

    abort(404);
})->where('path', '.*')->name('storage.local');

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.stripe');

Route::middleware('auth')->group(function () {
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/role', [\App\Http\Controllers\Admin\AdminUserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/categories', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::post('/e/{slug}/buy', [CheckoutController::class, 'createSession'])->name('orders.buy');
    Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::post('/events/{slug}/save', [\App\Http\Controllers\SaveEventController::class, 'store'])->name('events.save');
    Route::delete('/events/{slug}/save', [\App\Http\Controllers\SaveEventController::class, 'destroy'])->name('events.unsave');
    Route::post('/events/{slug}/reminders', [EventReminderController::class, 'store'])->name('events.reminders.store');
    Route::delete('/events/{slug}/reminders', [EventReminderController::class, 'destroy'])->name('events.reminders.destroy');
    Route::post('/vendors/{slug}/follow', [\App\Http\Controllers\FollowVendorController::class, 'store'])->name('vendors.follow');
    Route::delete('/vendors/{slug}/follow', [\App\Http\Controllers\FollowVendorController::class, 'destroy'])->name('vendors.unfollow');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/saved', [\App\Http\Controllers\Account\AccountSavedController::class, 'index'])->name('saved');
        Route::get('/tickets', [\App\Http\Controllers\Account\AccountTicketsController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [\App\Http\Controllers\Account\AccountTicketsController::class, 'show'])->name('tickets.show');
    });
});

require __DIR__.'/auth.php';
