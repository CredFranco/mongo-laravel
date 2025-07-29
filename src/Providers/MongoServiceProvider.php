<?php
    namespace Mongo\Providers;

    use Illuminate\Support\ServiceProvider;
use Mongo\Console\MakeMongoModelCommand;
use Mongo\MongoRepository;

    class MongoServiceProvider extends ServiceProvider
    {
        public function register()
        {
            $this->app->singleton('jarvis', function () {
                return new MongoRepository();
            });
            $this->commands([
                MakeMongoModelCommand::class,
            ]);
        }

        public function boot()
        {
            // Se quiser publicar configs no futuro
        }
    }
