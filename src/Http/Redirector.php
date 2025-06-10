<?php

declare(strict_types=1);

namespace Laravel\Lumen\Http;

use Illuminate\Http\RedirectResponse;
use Laravel\Lumen\Application;

class Redirector
{
  /**
   * Create a new redirector instance.
   *
   * @return void
   */
  public function __construct(
    /**
     * The application instance.
     */
    protected Application $app
  ) {}

  /**
   * Create a new redirect response to the given path.
   *
   * @param  string  $path
   * @param  int  $status
   * @param  array  $headers
   * @param  bool  $secure
   * @return RedirectResponse
   */
  public function to($path, $status = 302, $headers = [], $secure = null)
  {
    $path = $this->app->make('url')->to($path, [], $secure);

    return $this->createRedirect($path, $status, $headers);
  }

  /**
   * Create a new redirect response to a named route.
   *
   * @param  string  $route
   * @param  array  $parameters
   * @param  int  $status
   * @param  array  $headers
   * @return RedirectResponse
   */
  public function route($route, $parameters = [], $status = 302, $headers = [])
  {
    $path = $this->app->make('url')->route($route, $parameters);

    return $this->to($path, $status, $headers);
  }

  /**
   * Create a new redirect response.
   *
   * @param  string  $path
   * @param  int  $status
   * @param  array  $headers
   */
  protected function createRedirect($path, $status, $headers): RedirectResponse
  {
    $redirectResponse = new RedirectResponse($path, $status, $headers);

    $redirectResponse->setRequest($this->app->make('request'));

    return $redirectResponse;
  }
}
