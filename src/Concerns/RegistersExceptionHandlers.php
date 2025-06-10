<?php

declare(strict_types=1);

namespace Laravel\Lumen\Concerns;

use ErrorException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Log\LogManager;
use Laravel\Lumen\Exceptions\Handler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait RegistersExceptionHandlers
{
  /**
   * Throw an HttpException with the given data.
   *
   * @param  int  $code
   * @param  string  $message
   *
   * @throws HttpException
   */
  public function abort($code, $message = '', array $headers = []): void
  {
    if ($code == 404) {
      throw new NotFoundHttpException($message);
    }

    throw new HttpException($code, $message, null, $headers);
  }

  /**
   * Set the error handling for the application.
   *
   * @return void
   */
  protected function registerErrorHandling()
  {
    error_reporting(-1);

    set_error_handler(function ($level, $message, $file = '', $line = 0): void {
      $this->handleError($level, $message, $file, $line);
    });

    set_exception_handler(function ($e): void {
      $this->handleException($e);
    });

    register_shutdown_function(function (): void {
      $this->handleShutdown();
    });
  }

  /**
   * Report PHP deprecations, or convert PHP errors to ErrorException instances.
   *
   * @param  int  $level
   * @param  string  $message
   * @param  string  $file
   * @param  int  $line
   * @param  array  $context
   * @return void
   *
   * @throws ErrorException
   */
  public function handleError($level, $message, $file = '', $line = 0, $context = [])
  {
    if ((error_reporting() & $level) !== 0) {
      if ($this->isDeprecation($level)) {
        $this->handleDeprecation($message, $file, $line);

        return null;
      }

      throw new ErrorException($message, 0, $level, $file, $line);
    }

    return null;
  }

  /**
   * Reports a deprecation to the "deprecations" logger.
   *
   * @param  string  $message
   * @param  string  $file
   * @param  int  $line
   */
  public function handleDeprecation($message, $file, $line): void
  {
    try {
      $logger = $this->make('log');
    } catch (Exception) {
      return;
    }

    if (! $logger instanceof LogManager) {
      return;
    }

    $this->ensureDeprecationLoggerIsConfigured();

    with($logger->channel('deprecations'), function ($log) use ($message, $file, $line): void {
      $log->warning(sprintf('%s in %s on line %s',
        $message, $file, $line
      ));
    });
  }

  /**
   * Ensure the "deprecations" logger is configured.
   *
   * @return void
   */
  protected function ensureDeprecationLoggerIsConfigured()
  {
    with($this->make('config'), function ($config): void {
      if ($config->get('logging.channels.deprecations')) {
        return;
      }

      $driver = $config->get('logging.deprecations') ?? 'null';

      $config->set('logging.channels.deprecations', $config->get("logging.channels.{$driver}"));
    });
  }

  /**
   * Handle the PHP shutdown event.
   */
  public function handleShutdown(): void
  {
    if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
      $this->handleException($this->fatalErrorFromPhpError($error, 0));
    }
  }

  /**
   * Create a new fatal error instance from an error array.
   *
   * @param  int|null  $traceOffset
   */
  protected function fatalErrorFromPhpError(array $error, $traceOffset = null): FatalError
  {
    return new FatalError($error['message'], 0, $error, $traceOffset);
  }

  /**
   * Determine if the error level is a deprecation.
   *
   * @param  int  $level
   */
  protected function isDeprecation($level): bool
  {
    return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
  }

  /**
   * Determine if the error type is fatal.
   *
   * @param  int  $type
   */
  protected function isFatal($type): bool
  {
    return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
  }

  /**
   * Send the exception to the handler and return the response.
   *
   * @return Response
   */
  protected function sendExceptionToHandler(Throwable $throwable)
  {
    $handler = $this->resolveExceptionHandler();

    $handler->report($throwable);

    return $handler->render($this->make('request'), $throwable);
  }

  /**
   * Handle an uncaught exception instance.
   *
   * @return void
   */
  protected function handleException(Throwable $throwable)
  {
    $handler = $this->resolveExceptionHandler();

    $handler->report($throwable);

    if ($this->runningInConsole()) {
      $handler->renderForConsole(new ConsoleOutput, $throwable);
    } else {
      $handler->render($this->make('request'), $throwable)->send();
    }
  }

  /**
   * Get the exception handler from the container.
   *
   * @return ExceptionHandler
   */
  protected function resolveExceptionHandler()
  {
    if ($this->bound(ExceptionHandler::class)) {
      return $this->make(ExceptionHandler::class);
    }

    return $this->make(Handler::class);
  }
}
