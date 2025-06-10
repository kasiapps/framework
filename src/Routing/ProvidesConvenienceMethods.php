<?php

declare(strict_types=1);

namespace Laravel\Lumen\Routing;

use Closure as BaseClosure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

trait ProvidesConvenienceMethods
{
  /**
   * The response builder callback.
   *
   * @var \Closure
   */
  protected static $responseBuilder;

  /**
   * The error formatter callback.
   *
   * @var \Closure
   */
  protected static $errorFormatter;

  /**
   * Set the response builder callback.
   */
  public static function buildResponseUsing(BaseClosure $callback): void
  {
    static::$responseBuilder = $callback;
  }

  /**
   * Set the error formatter callback.
   */
  public static function formatErrorsUsing(BaseClosure $callback): void
  {
    static::$errorFormatter = $callback;
  }

  /**
   * Validate the given request with the given rules.
   *
   * @return array
   *
   * @throws ValidationException
   */
  public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
  {
    $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

    if ($validator->fails()) {
      $this->throwValidationException($request, $validator);
    }

    return $this->extractInputFromRules($request, $rules);
  }

  /**
   * Get the request input based on the given validation rules.
   *
   * @return array
   */
  protected function extractInputFromRules(Request $request, array $rules)
  {
    return $request->only(collect($rules)->keys()->map(fn ($rule) => Str::contains($rule, '.') ? explode('.', (string) $rule)[0] : $rule)->unique()->toArray());
  }

  /**
   * Throw the failed validation exception.
   *
   * @param  \Illuminate\Contracts\Validation\Validator  $validator
   *
   * @throws ValidationException
   */
  protected function throwValidationException(Request $request, $validator): never
  {
    throw new ValidationException($validator, $this->buildFailedValidationResponse(
      $request, $this->formatValidationErrors($validator)
    ));
  }

  /**
   * Build a response based on the given errors.
   *
   * @return JsonResponse|mixed
   */
  protected function buildFailedValidationResponse(Request $request, array $errors)
  {
    if (isset(static::$responseBuilder)) {
      return (static::$responseBuilder)($request, $errors);
    }

    return new JsonResponse($errors, 422);
  }

  /**
   * Format validation errors.
   *
   * @return array|mixed
   */
  protected function formatValidationErrors(Validator $validator)
  {
    if (isset(static::$errorFormatter)) {
      return (static::$errorFormatter)($validator);
    }

    return $validator->errors()->getMessages();
  }

  /**
   * Authorize a given action against a set of arguments.
   *
   * @param  mixed  $ability
   * @param  mixed|array  $arguments
   * @return Response
   *
   * @throws AuthorizationException
   */
  public function authorize($ability, $arguments = [])
  {
    [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

    return app(Gate::class)->authorize($ability, $arguments);
  }

  /**
   * Authorize a given action for a user.
   *
   * @param  Authenticatable|mixed  $user
   * @param  mixed  $ability
   * @param  mixed|array  $arguments
   * @return Response
   *
   * @throws AuthorizationException
   */
  public function authorizeForUser($user, $ability, $arguments = [])
  {
    [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

    return app(Gate::class)->forUser($user)->authorize($ability, $arguments);
  }

  /**
   * Guesses the ability's name if it wasn't provided.
   *
   * @param  mixed  $ability
   * @param  mixed|array  $arguments
   */
  protected function parseAbilityAndArguments($ability, $arguments): array
  {
    if (is_string($ability)) {
      return [$ability, $arguments];
    }

    return [debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'], $ability];
  }

  /**
   * Dispatch a job to its appropriate handler.
   *
   * @param  mixed  $job
   * @return mixed
   */
  public function dispatch($job)
  {
    return app(Dispatcher::class)->dispatch($job);
  }

  /**
   * Dispatch a command to its appropriate handler in the current process.
   *
   * @param  mixed  $job
   * @param  mixed  $handler
   * @return mixed
   */
  public function dispatchNow($job, $handler = null)
  {
    return app(Dispatcher::class)->dispatchNow($job, $handler);
  }

  /**
   * Get a validation factory instance.
   *
   * @return Factory
   */
  protected function getValidationFactory()
  {
    return app('validator');
  }
}
