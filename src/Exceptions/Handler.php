<?php

declare(strict_types=1);

namespace Laravel\Lumen\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Error;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler implements ExceptionHandler
{
  /**
   * A list of the exception types that should not be reported.
   *
   * @var array
   */
  protected $dontReport = [];

  /**
   * Report or log an exception.
   *
   *
   * @throws Exception
   */
  public function report(Throwable $throwable): void
  {
    if ($this->shouldntReport($throwable)) {
      return;
    }

    if (method_exists($throwable, 'report') && $throwable->report() !== false) {
      return;
    }

    try {
      $logger = app(LoggerInterface::class);
    } catch (Exception) {
      throw $throwable; // throw the original exception
    }

    $logger->error($throwable->getMessage(), ['exception' => $throwable]);
  }

  /**
   * Determine if the exception should be reported.
   *
   * @return bool
   */
  public function shouldReport(Throwable $throwable)
  {
    return ! $this->shouldntReport($throwable);
  }

  /**
   * Determine if the exception is in the "do not report" list.
   */
  protected function shouldntReport(Throwable $throwable): bool
  {
    foreach ($this->dontReport as $type) {
      if ($throwable instanceof $type) {
        return true;
      }
    }

    return false;
  }

  /**
   * Render an exception into an HTTP response.
   *
   * @param  Request  $request
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws Throwable
   */
  public function render($request, Throwable $throwable)
  {
    if (method_exists($throwable, 'render')) {
      return $throwable->render($request);
    }
    if ($throwable instanceof Responsable) {
      return $throwable->toResponse($request);
    }
    if ($throwable instanceof HttpResponseException) {
      return $throwable->getResponse();
    }

    if ($throwable instanceof ModelNotFoundException) {
      $throwable = new NotFoundHttpException($throwable->getMessage(), $throwable);
    } elseif ($throwable instanceof AuthorizationException) {
      $throwable = new HttpException($throwable->status() ?? 403, $throwable->getMessage());
    } elseif ($throwable instanceof ValidationException && $throwable->getResponse()) {
      return $throwable->getResponse();
    }

    return $request->expectsJson()
                    ? $this->prepareJsonResponse($request, $throwable)
                    : $this->prepareResponse($request, $throwable);
  }

  /**
   * Prepare a JSON response for the given exception.
   *
   * @param  Request  $request
   */
  protected function prepareJsonResponse($request, Throwable $throwable): JsonResponse
  {
    return new JsonResponse(
      $this->convertExceptionToArray($throwable),
      $this->isHttpException($throwable) ? $throwable->getStatusCode() : 500,
      $this->isHttpException($throwable) ? $throwable->getHeaders() : [],
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );
  }

  /**
   * Convert the given exception to an array.
   */
  protected function convertExceptionToArray(Throwable $throwable): array
  {
    return config('app.debug', false) ? [
      'message' => $throwable->getMessage(),
      'exception' => $throwable::class,
      'file' => $throwable->getFile(),
      'line' => $throwable->getLine(),
      'trace' => collect($throwable->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
    ] : [
      'message' => $this->isHttpException($throwable) ? $throwable->getMessage() : 'Server Error',
    ];
  }

  /**
   * Prepare a response for the given exception.
   *
   * @param  Request  $request
   */
  protected function prepareResponse($request, Throwable $throwable): Response
  {
    $response = new Response(
      $this->renderExceptionWithSymfony($throwable, config('app.debug', false)),
      $this->isHttpException($throwable) ? $throwable->getStatusCode() : 500,
      $this->isHttpException($throwable) ? $throwable->getHeaders() : []
    );

    $response->exception = $throwable;

    return $response;
  }

  /**
   * Render an exception to a string using Symfony.
   *
   * @param  bool  $debug
   */
  protected function renderExceptionWithSymfony(Throwable $throwable, $debug): string
  {
    $htmlErrorRenderer = new HtmlErrorRenderer($debug);

    return $htmlErrorRenderer->render($throwable)->getAsString();
  }

  /**
   * Render an exception to the console.
   *
   * @param  OutputInterface  $output
   */
  public function renderForConsole($output, Throwable $throwable): void
  {
    if ($throwable instanceof CommandNotFoundException) {
      $message = str($throwable->getMessage())->explode('.')->first();

      if (! empty($alternatives = $throwable->getAlternatives())) {
        $message .= '. Did you mean one of these?';

        with(new Error($output))->render($message);
        with(new BulletList($output))->render($throwable->getAlternatives());

        $output->writeln('');
      } else {
        with(new Error($output))->render($message);
      }

      return;
    }

    (new ConsoleApplication)->renderThrowable($throwable, $output);
  }

  /**
   * Determine if the given exception is an HTTP exception.
   *
   * @return bool
   */
  protected function isHttpException(Throwable $throwable)
  {
    return $throwable instanceof HttpExceptionInterface;
  }
}
