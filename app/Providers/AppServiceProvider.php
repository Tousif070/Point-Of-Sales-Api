<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MoneyTransaction\MoneyTransactionService;
use App\Services\CustomerAccountStatement\CustomerAccountStatementService;
use App\Services\Record\RecordService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        MoneyTransactionService::Handle($this->app);

        CustomerAccountStatementService::Handle($this->app);

        RecordService::Handle($this->app);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
