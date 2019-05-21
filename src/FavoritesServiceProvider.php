<?php namespace Sugar\Favorites;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Copyright (C) 2016 Gregory Claeyssens
 */
class FavoritesServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('command.favorites.clean', function ($app) {
            return new Console\CleanCommand();
        });
        $this->commands('command.favorites.clean');
    }

    /**
     * Bootstrap the application events.
     */
    public function boot() {
        $this->publishes([
            realpath(__DIR__ . '/../migrations') => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            realpath(__DIR__ . '/../config/favorites.php') => config_path('favorites.php')
        ], 'config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return array('favorites',
            'favorites.clean',
        );
    }
}