<?php

namespace Amuz\CustomSession;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CustomSessionServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/custom-session.php',
            'custom-session'
        );
    }

    public function boot()
    {

        $this->publishes([
            __DIR__ . '/../config/custom-session.php' => config_path('custom-session.php'),
        ], 'config');

        Session::extend('custom_database', function ($app) {
            $handler = new CustomDatabaseSessionHandler(
                DB::connection(config('session.connection')),
                config('session.table'),
                config('session.lifetime'),
                $app
            );

            $excludeRoutes = config('custom-session.exclude_routes', []);
            $handler->excludeRoutes($excludeRoutes);

            $handler->addExclusionCallback(function ($request) {
                return $request->is('api/no-session/*') ||
                    str_contains($request->header('User-Agent'), 'HealthBot');
            });

            return $handler;
        });
    }
}
