<?php

namespace Lin\LaravelScoutElasticsearch;

use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use Lin\LaravelScoutElasticsearch\Engine\ElasticsearchEngine;
use Lin\LaravelScoutElasticsearch\Console\ImportCommand;
use Lin\LaravelScoutElasticsearch\Console\FlushCommand;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // 注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCommand::class,
                FlushCommand::class,
            ]);
        }

        app(EngineManager::class)->extend('elasticsearch', function($app) {
            return new ElasticsearchEngine();
        });
    }

    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/scout.php', 'scout'
        );
    }
}
