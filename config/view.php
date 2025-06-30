<?php

return [

  /**
   * VIEW STORAGE PATHS
   *
   * Most templating systems load templates from disk. Here you may specify
   * an array of paths that should be checked for your views. Of course
   * the usual Kasi view path has already been registered for you.
   */
  'paths' => [
    resource_path('views'),
  ],

  /**
   * COMPILED VIEW PATH
   *
   * This option determines where all the compiled Blade templates will be
   * stored for your application. Typically, this is within the storage
   * directory. However, as usual, you are free to change this value.
   */
  'compiled' => realpath(storage_path('framework/views')),

];
