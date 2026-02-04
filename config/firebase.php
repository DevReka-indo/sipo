<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Firebase Project
    |--------------------------------------------------------------------------
    |
    | Nama project Firebase yang akan digunakan secara default.
    | Nilainya diambil dari .env → FIREBASE_PROJECT
    |
    */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project Configurations
    |--------------------------------------------------------------------------
    |
    | Setiap project Firebase didefinisikan di sini. Jika kamu hanya punya
    | satu project (seperti "sipo-161b4"), cukup biarkan satu blok ini.
    |
    */

    'projects' => [
        env('FIREBASE_PROJECT', 'app') => [
            /*
            |--------------------------------------------------------------------------
            | Credentials / Service Account
            |--------------------------------------------------------------------------
            |
            | Path file JSON service account Firebase kamu.
            | Ambil dari variabel environment FIREBASE_CREDENTIALS di .env
            |
            */

            'credentials' => [
                'file' => base_path(env('FIREBASE_CREDENTIALS')),
            ],

            /*
            |--------------------------------------------------------------------------
            | Firebase Auth
            |--------------------------------------------------------------------------
            */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Firestore Database
            |--------------------------------------------------------------------------
            */

            'firestore' => [
                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Realtime Database
            |--------------------------------------------------------------------------
            */

            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'),
                // 'auth_variable_override' => ['uid' => 'my-service-worker'],
            ],

            /*
            |--------------------------------------------------------------------------
            | Dynamic Links
            |--------------------------------------------------------------------------
            */

            'dynamic_links' => [
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Cloud Storage
            |--------------------------------------------------------------------------
            */

            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Caching
            |--------------------------------------------------------------------------
            */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
            |--------------------------------------------------------------------------
            | Logging
            |--------------------------------------------------------------------------
            */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
            |--------------------------------------------------------------------------
            | HTTP Client Options
            |--------------------------------------------------------------------------
            */

            'http_client_options' => [
                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),
                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),
                'guzzle_middlewares' => [],
            ],
        ],
    ],
];
