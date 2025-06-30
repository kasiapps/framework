<?php

return [

  /**
   * APPLICATION NAME
   *
   * This value is the name of your application. This value is used when the
   * framework needs to place the application's name in a notification or
   * any other location as required by the application or its packages.
   */
  'name' => env('APP_NAME', 'Kasi'),

  /**
   * APPLICATION ENVIRONMENT
   *
   * This value determines the "environment" your application is currently
   * running in. This may determine how you prefer to configure various
   * services the application utilizes. Set this in your ".env" file.
   */
  'env' => env('APP_ENV', 'production'),

  /**
   * APPLICATION DEBUG MODE
   *
   * When your application is in debug mode, detailed error messages with
   * stack traces will be shown on every error that occurs within your
   * application. If disabled, a simple generic error page is shown.
   */
  'debug' => (bool) env('APP_DEBUG', false),

  /**
   * APPLICATION URL
   *
   * This URL is used by the console to properly generate URLs when using
   * the Artisan command line tool. You should set this to the root of
   * your application so that it is used when running Artisan tasks.
   */
  'url' => env('APP_URL', 'http://localhost'),

  /**
   * APPLICATION TIMEZONE
   *
   * Here you may specify the default timezone for your application, which
   * will be used by the PHP date and date-time functions. We have gone
   * ahead and set this to a sensible default for you out of the box.
   */
  'timezone' => 'UTC',

  /**
   * APPLICATION LOCALE CONFIGURATION
   *
   * The application locale determines the default locale that will be used
   * by the translation service provider. You are free to set this value
   * to any of the locales which will be supported by the application.
   */
  'locale' => env('APP_LOCALE', 'en'),

  /**
   * APPLICATION FALLBACK LOCALE
   *
   * The fallback locale determines the locale to use when the current one
   * is not available. You may change the value to correspond to any of
   * the language folders that are provided through your application.
   */
  'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

  /**
   * ENCRYPTION KEY
   *
   * This key is used by the Illuminate encrypter service and should be set
   * to a random, 32 character string, otherwise these encrypted strings
   * will not be safe. Please do this before deploying an application!
   */
  'key' => env('APP_KEY'),
  'cipher' => 'AES-256-CBC',
];
