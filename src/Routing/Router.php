<?php

declare(strict_types=1);

namespace Laravel\Lumen\Routing;

use Closure;
use Illuminate\Support\Arr;
use Laravel\Lumen\Application;

class Router
{
  /**
   * The route group attribute stack.
   *
   * @var array
   */
  protected $groupStack = [];

  /**
   * All of the routes waiting to be registered.
   *
   * @var array
   */
  protected $routes = [];

  /**
   * All of the named routes and URI pairs.
   *
   * @var array
   */
  public $namedRoutes = [];

  /**
   * Router constructor.
   *
   * @param  Application  $app
   */
  public function __construct(
    /**
     * The application instance.
     */
    public $app
  ) {}

  /**
   * Register a set of routes with a set of shared attributes.
   */
  public function group(array $attributes, Closure $callback): void
  {
    if (isset($attributes['middleware']) && is_string($attributes['middleware'])) {
      $attributes['middleware'] = explode('|', $attributes['middleware']);
    }

    $this->updateGroupStack($attributes);

    $callback($this);

    array_pop($this->groupStack);
  }

  /**
   * Update the group stack with the given attributes.
   *
   * @return void
   */
  protected function updateGroupStack(array $attributes)
  {
    if (! empty($this->groupStack)) {
      $attributes = $this->mergeWithLastGroup($attributes);
    }

    $this->groupStack[] = $attributes;
  }

  /**
   * Merge the given group attributes.
   *
   * @param  array  $new
   * @param  array  $old
   */
  public function mergeGroup($new, $old): array
  {
    $new['namespace'] = static::formatUsesPrefix($new, $old);

    $new['prefix'] = static::formatGroupPrefix($new, $old);

    if (isset($new['domain'])) {
      unset($old['domain']);
    }

    if (isset($old['as'])) {
      $new['as'] = $old['as'].(isset($new['as']) ? '.'.$new['as'] : '');
    }

    if (isset($old['suffix']) && ! isset($new['suffix'])) {
      $new['suffix'] = $old['suffix'];
    }

    return array_merge_recursive(Arr::except($old, ['namespace', 'prefix', 'as', 'suffix']), $new);
  }

  /**
   * Merge the given group attributes with the last added group.
   *
   * @param  array  $new
   * @return array
   */
  protected function mergeWithLastGroup($new)
  {
    return $this->mergeGroup($new, end($this->groupStack));
  }

  /**
   * Format the uses prefix for the new group attributes.
   *
   * @param  array  $new
   * @param  array  $old
   * @return string|null
   */
  protected static function formatUsesPrefix($new, $old)
  {
    if (isset($new['namespace'])) {
      return isset($old['namespace']) && ! str_starts_with($new['namespace'], '\\')
          ? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
          : trim($new['namespace'], '\\');
    }

    return $old['namespace'] ?? null;
  }

  /**
   * Format the prefix for the new group attributes.
   *
   * @param  array  $new
   * @param  array  $old
   * @return string|null
   */
  protected static function formatGroupPrefix($new, $old)
  {
    $oldPrefix = $old['prefix'] ?? null;

    if (isset($new['prefix'])) {
      return trim($oldPrefix ?? '', '/').'/'.trim($new['prefix'], '/');
    }

    return $oldPrefix;
  }

  /**
   * Add a route to the collection.
   *
   * @param  array|string  $method
   * @param  string  $uri
   * @param  mixed  $action
   */
  public function addRoute($method, $uri, $action): void
  {
    $action = $this->parseAction($action);

    $attributes = null;

    if ($this->hasGroupStack()) {
      $attributes = $this->mergeWithLastGroup([]);
    }

    if (isset($attributes) && is_array($attributes)) {
      if (isset($attributes['prefix'])) {
        $uri = trim((string) $attributes['prefix'], '/').'/'.trim($uri, '/');
      }

      if (isset($attributes['suffix'])) {
        $uri = trim($uri, '/').rtrim($attributes['suffix'], '/');
      }

      $action = $this->mergeGroupAttributes($action, $attributes);
    }

    $uri = '/'.trim($uri, '/');

    if (isset($action['as'])) {
      $this->namedRoutes[$action['as']] = $uri;
    }

    if (is_array($method)) {
      foreach ($method as $verb) {
        $this->routes[$verb.$uri] = ['method' => $verb, 'uri' => $uri, 'action' => $action];
      }
    } else {
      $this->routes[$method.$uri] = ['method' => $method, 'uri' => $uri, 'action' => $action];
    }
  }

  /**
   * Parse the action into an array format.
   *
   * @param  mixed  $action
   */
  protected function parseAction($action): array
  {
    if (is_string($action)) {
      return ['uses' => $action];
    }
    if (! is_array($action)) {
      return [$action];
    }

    if (isset($action['middleware']) && is_string($action['middleware'])) {
      $action['middleware'] = explode('|', $action['middleware']);
    }

    return $action;
  }

  /**
   * Determine if the router currently has a group stack.
   */
  public function hasGroupStack(): bool
  {
    return ! empty($this->groupStack);
  }

  /**
   * Merge the group attributes into the action.
   *
   * @return array
   */
  protected function mergeGroupAttributes(array $action, array $attributes)
  {
    $namespace = $attributes['namespace'] ?? null;
    $middleware = $attributes['middleware'] ?? null;
    $as = $attributes['as'] ?? null;

    return $this->mergeNamespaceGroup(
      $this->mergeMiddlewareGroup(
        $this->mergeAsGroup($action, $as),
        $middleware),
      $namespace
    );
  }

  /**
   * Merge the namespace group into the action.
   *
   * @param  string  $namespace
   */
  protected function mergeNamespaceGroup(array $action, $namespace = null): array
  {
    if (isset($namespace, $action['uses'])) {
      $action['uses'] = $this->prependGroupNamespace($action['uses'], $namespace);
    }

    return $action;
  }

  /**
   * Prepend the namespace onto the use clause.
   *
   * @param  string  $namespace
   */
  protected function prependGroupNamespace(string $class, $namespace = null): string
  {
    return $namespace !== null && ! str_starts_with($class, '\\')
        ? $namespace.'\\'.$class : $class;
  }

  /**
   * Merge the middleware group into the action.
   *
   * @param  array  $middleware
   */
  protected function mergeMiddlewareGroup(array $action, $middleware = null): array
  {
    if (isset($middleware)) {
      $action['middleware'] = isset($action['middleware']) ? array_merge($middleware, $action['middleware']) : $middleware;
    }

    return $action;
  }

  /**
   * Merge the as group into the action.
   *
   * @param  string  $as
   */
  protected function mergeAsGroup(array $action, $as = null): array
  {
    if (isset($as) && ! empty($as)) {
      $action['as'] = isset($action['as']) ? $as.'.'.$action['as'] : $as;
    }

    return $action;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function head($uri, $action): static
  {
    $this->addRoute('HEAD', $uri, $action);

    return $this;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function get($uri, $action): static
  {
    $this->addRoute('GET', $uri, $action);

    return $this;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function post($uri, $action): static
  {
    $this->addRoute('POST', $uri, $action);

    return $this;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function put($uri, $action): static
  {
    $this->addRoute('PUT', $uri, $action);

    return $this;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function patch($uri, $action): static
  {
    $this->addRoute('PATCH', $uri, $action);

    return $this;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function delete($uri, $action): static
  {
    $this->addRoute('DELETE', $uri, $action);

    return $this;
  }

  /**
   * Register a route with the application.
   *
   * @param  string  $uri
   * @param  mixed  $action
   * @return $this
   */
  public function options($uri, $action): static
  {
    $this->addRoute('OPTIONS', $uri, $action);

    return $this;
  }

  /**
   * Get the raw routes for the application.
   *
   * @return array
   */
  public function getRoutes()
  {
    return $this->routes;
  }
}
