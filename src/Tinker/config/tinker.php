<?php

declare(strict_types=1);

return [

  /**
    * CONSOLE COMMANDS
    *
    * This option allows you to add additional Artisan commands that should
    * be available within the Tinker environment. Once the command is in
    * this array you may execute the command in Tinker using its name.
    *
    */

  'commands' => [
    // App\Console\Commands\ExampleCommand::class,
  ],

  /**
    * AUTO ALIASED CLASSES
    *
    * Tinker will not automatically alias classes in your vendor namespaces
    * but you may explicitly allow a subset of classes to get aliased by
    * adding the names of each of those classes to the following list.
    *
    */

  'alias' => [
    //
  ],

  /**
    * CLASSES THAT SHOULD NOT BE ALIASED
    *
    * Typically, Tinker automatically aliases classes as you require them in
    * Tinker. However, you may wish to never alias certain classes, which
    * you may accomplish by listing the classes in the following array.
    *
    */

  'dont_alias' => [
    'App\Nova',
  ],
];
