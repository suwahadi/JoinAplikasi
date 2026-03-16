<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\DuitkuCallbackController;

Route::post('/payment/duitku/callback', [DuitkuCallbackController::class, 'handle'])
    ->name('api.payment.duitku.callback');
