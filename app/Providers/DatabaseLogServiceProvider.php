<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use App\Logging\DatabaseLoggerHandler;
use Illuminate\Support\Facades\Log;

class DatabaseLogServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Log::getLogger()->pushHandler(
            $this->app->make(DatabaseLoggerHandler::class)
        );
    }
}
