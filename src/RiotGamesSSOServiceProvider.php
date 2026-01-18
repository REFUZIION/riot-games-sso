<?php

namespace Fuziion\RiotGamesSSO;

use Illuminate\Support\ServiceProvider;

class RiotGamesSSOServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/riot-games-sso.php',
            'riot-games-sso'
        );

        $this->app->singleton('riot-games-sso', function ($app) {
            return new RiotGames(
                config('riot-games-sso.client_id'),
                config('riot-games-sso.client_secret')
            );
        });

        $this->app->alias('riot-games-sso', RiotGames::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/riot-games-sso.php' => config_path('riot-games-sso.php'),
        ], 'riot-games-sso-config');
    }
}
