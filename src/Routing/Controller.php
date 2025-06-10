<?php

declare(strict_types=1);

namespace Laravel\Lumen\Routing;

class Controller
{
  use ProvidesConvenienceMethods;

  /**
   * The middleware defined on the controller.
   *
   * @var array
   */
  protected $middleware = [];

  /**
   * Define a middleware on the controller.
   *
   * @param  string  $middleware
   */
  public function middleware($middleware, array $options = []): void
  {
    $this->middleware[$middleware] = $options;
  }

  /**
   * Get the middleware for a given method.
   *
   * @param  string  $method
   */
  public function getMiddlewareForMethod($method): array
  {
    $middleware = [];

    foreach ($this->middleware as $name => $options) {
      if (isset($options['only']) && ! in_array($method, (array) $options['only'])) {
        continue;
      }

      if (isset($options['except']) && in_array($method, (array) $options['except'])) {
        continue;
      }

      $middleware[] = $name;
    }

    return $middleware;
  }
}
