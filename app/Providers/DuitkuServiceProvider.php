<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Payment\Duitku\Contracts\DuitkuCallbackHandlerInterface;
use App\Services\Payment\Duitku\DuitkuCallbackHandler;
use App\Services\Payment\Duitku\Contracts\DuitkuServiceInterface;
use App\Services\Payment\Duitku\DuitkuService;

class DuitkuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DuitkuCallbackHandlerInterface::class, DuitkuCallbackHandler::class);
        $this->app->bind(DuitkuServiceInterface::class, DuitkuService::class);
    }

    public function boot(): void
    {
        //
    }
}
