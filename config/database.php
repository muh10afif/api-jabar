<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql89' => [
            'driver' => 'mysql',
            'host' => env('DB2_HOST'),
            'database' => env('DB2_DATABASE'),
            'username' => env('DB2_USERNAME'),
            'password' => env('DB2_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql225' => [
            'driver' => 'mysql',
            'host' => env('DB3_HOST'),
            'database' => env('DB3_DATABASE'),
            'username' => env('DB3_USERNAME'),
            'password' => env('DB3_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql170' => [
            'driver' => 'mysql',
            'host' => env('DB4_HOST'),
            'database' => env('DB4_DATABASE'),
            'username' => env('DB4_USERNAME'),
            'password' => env('DB4_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql139' => [
            'driver' => 'mysql',
            'host' => env('DB5_HOST'),
            'database' => env('DB5_DATABASE'),
            'username' => env('DB5_USERNAME'),
            'password' => env('DB5_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql144' => [
            'driver' => 'mysql',
            'host' => env('DB6_HOST'),
            'database' => env('DB6_DATABASE'),
            'username' => env('DB6_USERNAME'),
            'password' => env('DB6_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql222' => [
            'driver' => 'mysql',
            'host' => env('DB7_HOST'),
            'database' => env('DB7_DATABASE'),
            'username' => env('DB7_USERNAME'),
            'password' => env('DB7_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
    ]
];
