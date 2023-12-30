<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'connection'   => 'central',
            'driver'       => 'database',
            'table'        => 'jobs',
            'queue'        => 'default',
            'retry_after'  => 90,
            'after_commit' => false,
        ],
        /**
         * Allows us to dispatch jobs which are FORCED to use the central connection.
         * If we had some logic that needs to update the central context, use this
         * connection so we can be sure it's not running in a tenant context.
         *
         * dispatch(new SomeJob(...))->onConnection('central');
         */
        'central' => [
            'connection'   => 'central',
            'driver'       => 'database',
            'table'        => 'jobs',
            'queue'        => 'default',
            'retry_after'  => 90,
            'after_commit' => false,
            'central'      => true,
        ],

        'beanstalkd' => [
            'driver'       => 'beanstalkd',
            'host'         => 'localhost',
            'queue'        => 'default',
            'retry_after'  => 90,
            'block_for'    => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver'       => 'sqs',
            'key'          => env('AWS_ACCESS_KEY_ID'),
            'secret'       => env('AWS_SECRET_ACCESS_KEY'),
            'prefix'       => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue'        => env('SQS_QUEUE', 'default'),
            'suffix'       => env('SQS_SUFFIX'),
            'region'       => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver'       => 'redis',
            'connection'   => 'default',
            'queue'        => env('REDIS_QUEUE', 'default'),
            'retry_after'  => 90,
            'block_for'    => null,
            'after_commit' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table'    => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver'   => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table'    => 'failed_jobs',
    ],

];
