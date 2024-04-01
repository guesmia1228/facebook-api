<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '51.91.153.125'),
            'port' => env('DB_PORT', '3301'),
            'database' => env('DB_DATABASE', 'app_thesoci_9c37'),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'mysql_stats' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_STATS', '51.91.153.125'),
            'port' => env('DB_PORT_STATS', '3301'),
            'database' => env('DB_DATABASE_STATS', 'adsconcierge_stats'),
            'username' => env('DB_USERNAME_STATS', ''),
            'password' => env('DB_PASSWORD_STATS', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],

];
