<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bytez API Key
    |--------------------------------------------------------------------------
    |
    | Your Bytez API key. You can get this from the Bytez API Dashboard.
    | This key is required for all API requests.
    |
    */
    'api_key' => env('BYTEZ_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Bytez API. Defaults to the production API.
    | You typically don't need to change this unless you're using a custom endpoint.
    |
    */
    'base_url' => env('BYTEZ_BASE_URL', 'https://api.bytez.com'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests. Defaults to 30 seconds.
    |
    */
    'timeout' => env('BYTEZ_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic retries on failed requests.
    |
    */
    'retry_attempts' => env('BYTEZ_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('BYTEZ_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching API responses. This can help reduce API calls
    | and improve performance.
    |
    */
    'cache' => [
        'enabled' => env('BYTEZ_CACHE_ENABLED', true),
        'ttl' => env('BYTEZ_CACHE_TTL', 3600), // seconds
        'prefix' => env('BYTEZ_CACHE_PREFIX', 'bytez'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Keys
    |--------------------------------------------------------------------------
    |
    | Default provider keys for closed-source models. You can also set these
    | per-request using the withProviderKey() method.
    |
    | Example:
    | 'provider_keys' => [
    |     'openai' => env('OPENAI_API_KEY'),
    |     'anthropic' => env('ANTHROPIC_API_KEY'),
    | ],
    |
    */
    'provider_keys' => [
        // 'openai' => env('OPENAI_API_KEY'),
        // 'anthropic' => env('ANTHROPIC_API_KEY'),
    ],
];
