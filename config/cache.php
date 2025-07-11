<?php

use Illuminate\Support\Str;

return [

  /**
   * DEFAULT CACHE STORE
   *
   * This option controls the default cache connection that gets used while
   * using this caching library. This connection is used when another is
   * not explicitly specified when executing a given caching function.
   *
   * Supported: "apc", "array", "database", "file", "memcached", "redis"
   */
  'default' => env('CACHE_DRIVER', 'file'),

  /**
   * CACHE STORES
   *
   * Here you may define all of the cache "stores" for your application as
   * well as their drivers. You may even define multiple stores for the
   * same cache driver to group types of items stored in your caches.
   */
  'stores' => [

    'apc' => [
      'driver' => 'apc',
    ],

    'array' => [
      'driver' => 'array',
    ],

    'database' => [
      'driver' => 'database',
      'table' => env('CACHE_DATABASE_TABLE', 'cache'),
      'connection' => env('CACHE_DATABASE_CONNECTION'),
    ],

    'file' => [
      'driver' => 'file',
      'path' => storage_path('framework/cache/data'),
    ],

    'memcached' => [
      'driver' => 'memcached',
      'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
      'sasl' => [
        env('MEMCACHED_USERNAME'),
        env('MEMCACHED_PASSWORD'),
      ],
      'options' => [
        // Memcached::OPT_CONNECT_TIMEOUT => 2000,
      ],
      'servers' => [
        [
          'host' => env('MEMCACHED_HOST', '127.0.0.1'),
          'port' => env('MEMCACHED_PORT', 11211),
          'weight' => 100,
        ],
      ],
    ],

    'redis' => [
      'driver' => 'redis',
      'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
    ],

  ],

  /**
   * CACHE KEY PREFIX
   *
   * When utilizing a RAM based store such as APC or Memcached, there might
   * be other applications utilizing the same cache. So, we'll specify a
   * value to get prefixed to all our keys so we can avoid collisions.
   */
  'prefix' => env(
    'CACHE_PREFIX',
    Str::slug(env('APP_NAME', 'kasi'), '_').'_cache'
  ),

];
