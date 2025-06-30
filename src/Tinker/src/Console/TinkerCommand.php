<?php

declare(strict_types=1);

namespace Kasi\Tinker\Console;

use Kasi\Console\Command;
use Kasi\Database\Eloquent\Model;
use Kasi\Support\Collection;
use Kasi\Support\Env;
use Kasi\Support\HtmlString;
use Kasi\Support\Stringable;
use Kasi\Tinker\ClassAliasAutoloader;
use Psy\Configuration;
use Psy\Shell;
use Psy\VersionUpdater\Checker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TinkerCommand extends Command
{
  /**
   * Artisan commands to include in the tinker shell.
   *
   * @var array
   */
  protected $commandWhitelist = [
    'clear-compiled', 'down', 'env', 'inspire', 'migrate', 'migrate:install', 'optimize', 'up',
  ];

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'tinker';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Interact with your application';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $this->getApplication()->setCatchExceptions(false);

    $config = Configuration::fromInput($this->input);
    $config->setUpdateCheck(Checker::NEVER);

    $config->getPresenter()->addCasters(
      $this->getCasters()
    );

    if ($this->option('execute')) {
      $config->setRawOutput(true);
    }

    $shell = new Shell($config);
    $shell->addCommands($this->getCommands());
    $shell->setIncludes($this->argument('include'));

    $path = Env::get('COMPOSER_VENDOR_DIR', $this->getKasi()->basePath().DIRECTORY_SEPARATOR.'vendor');

    $path .= '/composer/autoload_classmap.php';

    $config = $this->getKasi()->make('config');

    $classAliasAutoloader = ClassAliasAutoloader::register(
      $shell, $path, $config->get('tinker.alias', []), $config->get('tinker.dont_alias', [])
    );

    if ($code = $this->option('execute')) {
      try {
        $shell->setOutput($this->output);
        $shell->execute($code);
      } finally {
        $classAliasAutoloader->unregister();
      }

      return 0;
    }

    try {
      return $shell->run();
    } finally {
      $classAliasAutoloader->unregister();
    }
  }

  /**
   * Get artisan commands to pass through to PsySH.
   */
  protected function getCommands(): array
  {
    $commands = [];

    foreach ($this->getApplication()->all() as $name => $command) {
      if (in_array($name, $this->commandWhitelist)) {
        $commands[] = $command;
      }
    }

    $config = $this->getKasi()->make('config');

    foreach ($config->get('tinker.commands', []) as $command) {
      $commands[] = $this->getApplication()->add(
        $this->getKasi()->make($command)
      );
    }

    return $commands;
  }

  /**
   * Get an array of Kasi tailored casters.
   */
  protected function getCasters(): array
  {
    $casters = [
      Collection::class => 'Kasi\Tinker\TinkerCaster::castCollection',
      HtmlString::class => 'Kasi\Tinker\TinkerCaster::castHtmlString',
      Stringable::class => 'Kasi\Tinker\TinkerCaster::castStringable',
    ];

    if (class_exists(Model::class)) {
      $casters[Model::class] = 'Kasi\Tinker\TinkerCaster::castModel';
    }

    if (class_exists('Kasi\Process\ProcessResult')) {
      $casters['Kasi\Process\ProcessResult'] = 'Kasi\Tinker\TinkerCaster::castProcessResult';
    }

    if (class_exists('Kasi\Foundation\Application')) {
      $casters['Kasi\Foundation\Application'] = 'Kasi\Tinker\TinkerCaster::castApplication';
    }

    $config = $this->getKasi()->make('config');

    return array_merge($casters, (array) $config->get('tinker.casters', []));
  }

  /**
   * Get the console command arguments.
   */
  protected function getArguments(): array
  {
    return [
      ['include', InputArgument::IS_ARRAY, 'Include file(s) before starting tinker'],
    ];
  }

  /**
   * Get the console command options.
   */
  protected function getOptions(): array
  {
    return [
      ['execute', null, InputOption::VALUE_OPTIONAL, 'Execute the given code using Tinker'],
    ];
  }
}
