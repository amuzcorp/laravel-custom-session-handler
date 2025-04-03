<?php

namespace Amuz\CustomSession;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CustomSessionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Session::extend('custom_database', function ($app) {
            $handler = new CustomDatabaseSessionHandler(
                DB::connection(config('session.connection')),
                config('session.table'),
                config('session.lifetime'),
                $app
            );

            // exclude by route names
            $handler->excludeRoutes([
                'health.check',
            ]);

            // exclude by user-defined callback
            $handler->addExclusionCallback(function ($request) {
                return str_contains($request->header('User-Agent'), 'HealthChecker');
            });

            return $handler;
        });
    }
}
