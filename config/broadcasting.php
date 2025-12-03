<?php

return [

    'default' => env('BROADCAST_CONNECTION', 'log'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => (function () {
                $host = env('PUSHER_HOST');

                return array_filter([
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'host' => $host ?: null,
                    // Only apply custom port/scheme when a host override exists.
                    'port' => $host ? env('PUSHER_PORT', 6001) : null,
                    'scheme' => $host ? env('PUSHER_SCHEME', 'http') : null,
                    'useTLS' => $host ? env('PUSHER_SCHEME', 'http') === 'https' : true,
                ], fn ($value) => ! is_null($value));
            })(),
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],

];
