<?php

use App\Livewire\Pages\DashboardPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\OrderPage;
use App\Livewire\Pages\ProductDetailPage;
use App\Livewire\Member\DeliveriesPage;
use App\Livewire\Member\OrdersPage;
use App\Livewire\Pages\ProfilePage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');

Route::get('/produk/{product:slug}', ProductDetailPage::class)
    ->name('products.show');

Route::get('/order/{transaction:uuid}', OrderPage::class)
    ->middleware(['auth'])
    ->name('orders.show');

Route::get('/dashboard', DashboardPage::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/profile', ProfilePage::class)
    ->middleware(['auth'])
    ->name('profile');

// Member deliveries (non-Filament)
Route::get('/member/deliveries', DeliveriesPage::class)
    ->middleware(['auth'])
    ->name('member.deliveries');

Route::get('/member/deliveries/{deliveryItem}', \App\Livewire\Member\DeliveryItemShowPage::class)
    ->middleware(['auth'])
    ->name('member.deliveries.show');

// Orders history (non-Filament)
Route::get('/orders', OrdersPage::class)
    ->middleware(['auth'])
    ->name('member.orders');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');

require __DIR__.'/auth.php';
