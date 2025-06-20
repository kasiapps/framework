<?php

declare(strict_types=1);

namespace Laravel\Lumen\Routing;

use Closure as BaseClosure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline as BasePipeline;
use Throwable;

/**
 * This extended pipeline catches any exceptions that occur during each slice.
 *
 * The exceptions are converted to HTTP responses for proper middleware handling.
 */
class Pipeline extends BasePipeline
{
  /**
   * Get a Closure that represents a slice of the application onion.
   *
   * @return \Closure
   */
  protected function carry()
  {
    return fn ($stack, $pipe): BaseClosure => function ($passable) use ($stack, $pipe) {
      try {
        $slice = parent::carry();

        return ($slice($stack, $pipe))($passable);
      } catch (Throwable $e) {
        return $this->handleException($passable, $e);
      }
    };
  }

  /**
   * Get the initial slice to begin the stack call.
   *
   * @return \Closure
   */
  protected function prepareDestination(BaseClosure $destination)
  {
    return function ($passable) use ($destination) {
      try {
        return $destination($passable);
      } catch (Throwable $e) {
        return $this->handleException($passable, $e);
      }
    };
  }

  /**
   * Handle the given exception.
   *
   * @param  mixed  $passable
   * @return mixed
   */
  protected function handleException($passable, Throwable $throwable)
  {
    if (! $this->container->bound(ExceptionHandler::class) || ! $passable instanceof Request) {
      throw $throwable;
    }

    $exceptionHandler = $this->container->make(ExceptionHandler::class);

    $exceptionHandler->report($throwable);

    return $exceptionHandler->render($passable, $throwable);
  }
}
