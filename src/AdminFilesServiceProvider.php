<?php

namespace LaraMod\Admin\Files;

use Faker\Provider\Image;
use Illuminate\Support\ServiceProvider;

class AdminFilesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'adminfiles');
        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/laramod/admin/files'),
        ]);
        $this->publishes([
            __DIR__.'/assets' => public_path('assets/laramod/dashboard'),
        ], 'public');
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');


    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
    }
}
