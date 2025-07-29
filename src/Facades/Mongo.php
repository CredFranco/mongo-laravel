<?php

    namespace Mongo\Facades;


    class Mongo
    {
        protected static string $collection;

        public static function __callStatic($method, $parameters)
        {
            return app(\Mongo\MongoRepository::class)
                ->setCollection(static::$collection)
                ->$method(...$parameters);
        }
    }
