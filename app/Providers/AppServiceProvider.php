<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Transaction;
use App\Models\Group;
use App\Models\GroupMember;
use App\Observers\TransactionObserver;
use App\Observers\GroupObserver;
use App\Observers\GroupMemberObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        Group::observe(GroupObserver::class);
        GroupMember::observe(GroupMemberObserver::class);
    }
}
