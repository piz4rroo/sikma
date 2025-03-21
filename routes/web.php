<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\PromoController as CustomerPromoController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\PaymentController;

// ✅ Landing Page
Route::get('/', [CustomerHomeController::class, 'index'])->name('home');

// ✅ Authentication Routes
Auth::routes();

// ✅ Guest Routes (Hanya bisa diakses jika belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ✅ Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resources([
        'menus' => MenuController::class,
        'orders' => OrderController::class,
        'customers' => CustomerController::class,
        'promos' => PromoController::class,
        'comments' => CommentController::class,
        'deliveries' => DeliveryController::class,
    ]);
    Route::post('/backup', [BackupController::class, 'create'])->name('backup');
});

// ✅ Customer Routes
Route::middleware(['auth', 'role:customer'])->prefix('customer')->as('customer.')->group(function () {
    Route::get('/', [CustomerHomeController::class, 'index'])->name('dashboard');

    // ✅ Menu Routes
    Route::get('/menus', [CustomerMenuController::class, 'index'])->name('menus.index');
    Route::get('/menus/{menu}', [CustomerMenuController::class, 'show'])->name('menus.show');

    // ✅ Promo Routes (Menggunakan `Route::resource` untuk fleksibilitas)
    Route::resource('promos', CustomerPromoController::class)->only(['index', 'show']);

    // ✅ Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

    // ✅ Orders & Reviews
    Route::resource('orders', CustomerOrderController::class);
    Route::resource('reviews', ReviewController::class);

    // ✅ Payments
    Route::get('/payments/create/{order}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');
});
