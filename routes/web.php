<?php

use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\OrderPage;
use App\Livewire\Pages\ProductDetailPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');

Route::get('/produk/{product:slug}', ProductDetailPage::class)
    ->name('products.show');

Route::get('/order/{transaction:uuid}', OrderPage::class)
    ->middleware(['auth'])
    ->name('orders.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
