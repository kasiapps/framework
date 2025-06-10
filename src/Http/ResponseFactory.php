<?php

declare(strict_types=1);

namespace Laravel\Lumen\Http;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseFactory
{
  use Macroable;

  /**
   * Return a new response from the application.
   *
   * @param  string  $content
   * @param  int  $status
   */
  public function make($content = '', $status = 200, array $headers = []): Response
  {
    return new Response($content, $status, $headers);
  }

  /**
   * Return a new JSON response from the application.
   *
   * @param  mixed  $data
   * @param  int  $status
   * @param  int  $options
   */
  public function json($data = [], $status = 200, array $headers = [], $options = 0): JsonResponse
  {
    return new JsonResponse($data, $status, $headers, $options);
  }

  /**
   * Create a new JSONP response instance.
   *
   * @param  string  $callback
   * @param  mixed  $data
   * @param  int  $status
   * @param  int  $options
   * @return JsonResponse
   */
  public function jsonp(?string $callback, $data = [], $status = 200, array $headers = [], $options = 0): \Symfony\Component\HttpFoundation\JsonResponse
  {
    return $this->json($data, $status, $headers, $options)->setCallback($callback);
  }

  /**
   * Create a new streamed response instance.
   *
   * @param  Closure  $callback
   * @param  int  $status
   */
  public function stream($callback, $status = 200, array $headers = []): StreamedResponse
  {
    return new StreamedResponse($callback, $status, $headers);
  }

  /**
   * Create a new streamed response instance as a file download.
   *
   * @param  Closure  $callback
   * @param  string|null  $name
   * @param  string|null  $disposition
   */
  public function streamDownload($callback, $name = null, array $headers = [], string $disposition = 'attachment'): StreamedResponse
  {
    $streamedResponse = new StreamedResponse($callback, 200, $headers);

    if (! is_null($name)) {
      $streamedResponse->headers->set('Content-Disposition', $streamedResponse->headers->makeDisposition(
        $disposition,
        $name,
        $this->fallbackName($name)
      ));
    }

    return $streamedResponse;
  }

  /**
   * Create a new file download response.
   *
   * @param  SplFileInfo|string  $file
   * @param  string  $name
   * @param  null|string  $disposition
   * @return BinaryFileResponse
   */
  public function download($file, $name = null, array $headers = [], string $disposition = 'attachment')
  {
    $binaryFileResponse = new BinaryFileResponse($file, 200, $headers, true, $disposition);

    if (! is_null($name)) {
      return $binaryFileResponse->setContentDisposition($disposition, $name, $this->fallbackName($name));
    }

    return $binaryFileResponse;
  }

  /**
   * Convert the string to ASCII characters that are equivalent to the given name.
   *
   * @param  string  $name
   */
  protected function fallbackName($name): string
  {
    return str_replace('%', '', Str::ascii($name));
  }

  /**
   * Return the raw contents of a binary file.
   *
   * @param  SplFileInfo|string  $file
   */
  public function file($file, array $headers = []): BinaryFileResponse
  {
    return new BinaryFileResponse($file, 200, $headers);
  }
}
