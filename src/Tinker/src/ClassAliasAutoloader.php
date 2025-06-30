<?php

declare(strict_types=1);

namespace Kasi\Tinker;

use Kasi\Support\Collection;
use Kasi\Support\Str;
use Psy\Shell;

class ClassAliasAutoloader
{
  /**
   * The shell instance.
   *
   * @var \Psy\Shell
   */
  protected $shell;

  /**
   * All of the discovered classes.
   *
   * @var array
   */
  protected $classes = [];

  /**
   * Path to the vendor directory.
   */
  protected string $vendorPath;

  /**
   * Explicitly included namespaces/classes.
   */
  protected Collection $includedAliases;

  /**
   * Excluded namespaces/classes.
   */
  protected Collection $excludedAliases;

  /**
   * Register a new alias loader instance.
   *
   * @param  string  $classMapPath
   * @return static
   */
  public static function register(Shell $shell, $classMapPath, array $includedAliases = [], array $excludedAliases = [])
  {
    return tap(new static($shell, $classMapPath, $includedAliases, $excludedAliases), function ($loader): void {
      spl_autoload_register([$loader, 'aliasClass']);
    });
  }

  /**
   * Create a new alias loader instance.
   *
   * @param  string  $classMapPath
   * @return void
   */
  public function __construct(Shell $shell, $classMapPath, array $includedAliases = [], array $excludedAliases = [])
  {
    $this->shell = $shell;
    $this->vendorPath = dirname($classMapPath, 2);
    $this->includedAliases = collect($includedAliases);
    $this->excludedAliases = collect($excludedAliases);

    $classes = require $classMapPath;

    foreach ($classes as $class => $path) {
      if (! $this->isAliasable($class, $path)) {
        continue;
      }

      $name = class_basename($class);

      if (! isset($this->classes[$name])) {
        $this->classes[$name] = $class;
      }
    }
  }

  /**
   * Find the closest class by name.
   *
   * @param  string  $class
   */
  public function aliasClass($class): void
  {
    if (Str::contains($class, '\\')) {
      return;
    }

    $fullName = $this->classes[$class] ?? false;

    if ($fullName) {
      $this->shell->writeStdout("[!] Aliasing '{$class}' to '{$fullName}' for this Tinker session.\n");

      class_alias($fullName, $class);
    }
  }

  /**
   * Unregister the alias loader instance.
   */
  public function unregister(): void
  {
    spl_autoload_unregister($this->aliasClass(...));
  }

  /**
   * Handle the destruction of the instance.
   *
   * @return void
   */
  public function __destruct()
  {
    $this->unregister();
  }

  /**
   * Whether a class may be aliased.
   *
   * @param  string  $class
   * @param  string  $path
   */
  public function isAliasable($class, $path)
  {
    if (! Str::contains($class, '\\')) {
      return false;
    }

    if (! $this->includedAliases->filter(fn ($alias) => Str::startsWith($class, $alias))->isEmpty()) {
      return true;
    }

    if (Str::startsWith($path, $this->vendorPath)) {
      return false;
    }

    return $this->excludedAliases->filter(fn ($alias) => Str::startsWith($class, $alias))->isEmpty();
  }
}
