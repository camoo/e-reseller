<?php
return [
    'debug' => true,
    'App' => [
        'some_config' => 'here',
    ],
    'Database' => [
        'default' => [
            'database' => 'my_db',
            'username' => 'my_dbuser',
            'password' => 'secret',
            'host' => 'localhost',
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            // 'port' => '3306',
        ],
    ],
    'Cache' => [
        /**
         * Configure the cache for model and datasource caches. This cache
         * configuration is used to store schema descriptions, and table listings
         * in connections.
         */
        '_camoo_model_' => [
            'className' => 'Cake\Cache\Engine\FileEngine',
            'duration'  => '+10 minutes',
            'serialize' => true,
            'prefix'    => 'orm_',
            'path'      => CACHE . 'persistent/model/',
        ],
    ],
    'Session' => [
        'name' => 'CAMOOFMW',
        'cookie' => [
            'expire' => 14400,
            'path' => '/',
            'domain' => '.camoo.cm',
            'secure' => true,
            'httponly' => true,
        ]
    ]
];
