'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('MONGO_HOST', '162.214.70.233'),
            'port'     => env('MONGO_PORT', 27017),
            'username' => env('MONGO_USER', 'dev'),
            'password' => env('MONGO_PASS', 'SenhaMuitoSegura123'),
            'options'  => [
                'database' => env('MONGO_DB', 'credit_engine'),
            ],
        ],