<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/products', [StorefrontController::class, 'products'])->name('products.index');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('products.show');
Route::get('/go/{product:slug}', [StorefrontController::class, 'affiliateRedirect'])->name('affiliate.redirect');
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:3,60')->name('reviews.store');
Route::get('/checkout/{product:slug}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{product:slug}/paypal-order', [CheckoutController::class, 'createPayPalOrder'])->middleware('throttle:8,10')->name('checkout.paypal-order');
Route::post('/checkout/paypal/capture', [CheckoutController::class, 'capturePayPalOrder'])->middleware('throttle:8,10')->name('checkout.paypal-capture');
Route::get('/checkout/complete/{reference}', [CheckoutController::class, 'complete'])->name('checkout.complete');
Route::get('/download/{token}', [CheckoutController::class, 'download'])->name('downloads.show');
Route::get('/robots.txt', [StorefrontController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('sitemap');
Route::get('/{page}', [StorefrontController::class, 'page'])->whereIn('page', ['affiliate-disclosure', 'privacy', 'terms'])->name('pages.show');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}/{status}', [AdminReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    });
});
