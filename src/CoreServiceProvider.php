<?php

namespace Cloudteam\Core;

use Cloudteam\{Core\Console\Commands\CleanJsCacheCommand,
    Core\Console\Commands\CreateMultipleMigration,
    Core\Console\Commands\CrudControllerCommand,
    Core\Console\Commands\CrudMakeCommand,
    Core\Console\Commands\CrudTableCommand,
    Core\Console\Commands\CrudTestCommand,
    Core\Console\Commands\CrudViewCommand,
    Core\Console\Commands\GenerateMultipleModel,
    Core\Console\Commands\InstallHooks,
    Core\Console\Commands\MakeEnumCommand,
    Core\Console\Commands\MakeLocalScopeCommand,
    Core\Console\Commands\MakeModelAttributeCommand,
    Core\Console\Commands\MakeModelMethodCommand,
    Core\Console\Commands\MakeModelRelationshipCommand,
    Core\Console\Commands\MakeModelServiceCommand,
    Core\Console\Commands\MakeMultipleMigration,
    Core\Console\Commands\MakeMultipleModel,
    Core\Console\Commands\PreCommitHook};
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cloudteam');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'cloudteam');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/core.php', 'core');

        // Register the service the package provides.
        $this->app->singleton('core', function ($app) {
            return new Core;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['core'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/core.php' => config_path('core.php'),
        ], 'core.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/cloudteam'),
        ], 'core.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/cloudteam'),
        ], 'core.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/cloudteam'),
        ], 'core.views');*/

        // Registering package commands.
        $this->commands([
            CrudMakeCommand::class,
            CrudControllerCommand::class,
            CrudTableCommand::class,
            CrudViewCommand::class,
            CrudTestCommand::class,

            MakeLocalScopeCommand::class,
            MakeModelMethodCommand::class,
            MakeModelAttributeCommand::class,
            MakeModelRelationshipCommand::class,
            MakeModelServiceCommand::class,
            MakeEnumCommand::class,

            MakeMultipleMigration::class,
            MakeMultipleModel::class,

            CleanJsCacheCommand::class,

            CreateMultipleMigration::class,
            GenerateMultipleModel::class,

            PreCommitHook::class,
            InstallHooks::class,
        ]);
    }
}
