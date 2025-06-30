<?php

declare(strict_types=1);

namespace Kasi\Tinker;

use Exception;
use Kasi\Database\Eloquent\Model;
use Kasi\Support\Collection;
use Kasi\Support\HtmlString;
use Kasi\Support\Stringable;
use Symfony\Component\VarDumper\Caster\Caster;

class TinkerCaster
{
  /**
   * Application methods to include in the presenter.
   */
  private static array $appProperties = [
    'configurationIsCached',
    'environment',
    'environmentFile',
    'isLocal',
    'routesAreCached',
    'runningUnitTests',
    'version',
    'path',
    'basePath',
    'configPath',
    'databasePath',
    'langPath',
    'publicPath',
    'storagePath',
    'bootstrapPath',
  ];

  /**
   * Get an array representing the properties of an application.
   *
   * @param  \Kasi\Foundation\Application  $app
   */
  public static function castApplication($app): array
  {
    $results = [];

    foreach (self::$appProperties as $appProperty) {
      try {
        $val = $app->{$appProperty}();

        if (! is_null($val)) {
          $results[Caster::PREFIX_VIRTUAL.$appProperty] = $val;
        }
      } catch (Exception) {
        //
      }
    }

    return $results;
  }

  /**
   * Get an array representing the properties of a collection.
   *
   * @param  Collection  $collection
   */
  public static function castCollection($collection): array
  {
    return [
      Caster::PREFIX_VIRTUAL.'all' => $collection->all(),
    ];
  }

  /**
   * Get an array representing the properties of an html string.
   *
   * @param  HtmlString  $htmlString
   */
  public static function castHtmlString($htmlString): array
  {
    return [
      Caster::PREFIX_VIRTUAL.'html' => $htmlString->toHtml(),
    ];
  }

  /**
   * Get an array representing the properties of a fluent string.
   *
   * @param  Stringable  $stringable
   */
  public static function castStringable($stringable): array
  {
    return [
      Caster::PREFIX_VIRTUAL.'value' => (string) $stringable,
    ];
  }

  /**
   * Get an array representing the properties of a process result.
   *
   * @param  \Kasi\Process\ProcessResult  $result
   */
  public static function castProcessResult($result): array
  {
    return [
      Caster::PREFIX_VIRTUAL.'output' => $result->output(),
      Caster::PREFIX_VIRTUAL.'errorOutput' => $result->errorOutput(),
      Caster::PREFIX_VIRTUAL.'exitCode' => $result->exitCode(),
      Caster::PREFIX_VIRTUAL.'successful' => $result->successful(),
    ];
  }

  /**
   * Get an array representing the properties of a model.
   *
   * @param  Model  $model
   */
  public static function castModel($model): array
  {
    $attributes = array_merge(
      $model->getAttributes(), $model->getRelations()
    );

    $visible = array_flip(
      $model->getVisible() ?: array_diff(array_keys($attributes), $model->getHidden())
    );

    $hidden = array_flip($model->getHidden());

    $appends = (function (): array {
      return array_combine($this->appends, $this->appends); // @phpstan-ignore-line
    })->bindTo($model, $model)();

    foreach ($appends as $append) {
      $attributes[$append] = $model->{$append};
    }

    $results = [];

    foreach ($attributes as $key => $value) {
      $prefix = '';

      if (isset($visible[$key])) {
        $prefix = Caster::PREFIX_VIRTUAL;
      }

      if (isset($hidden[$key])) {
        $prefix = Caster::PREFIX_PROTECTED;
      }

      $results[$prefix.$key] = $value;
    }

    return $results;
  }
}
