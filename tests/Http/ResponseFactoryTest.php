<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

it('makes default response', function () {
  $content = 'hello';
  $responseFactory = new ResponseFactory;
  $response = $responseFactory->make($content);

  expect($response)->toBeInstanceOf(SymfonyResponse::class);
  expect($response->getContent())->toBe($content);
  expect($response->getStatusCode())->toBe(Response::HTTP_OK);
});

it('creates json default response', function () {
  $content = ['hello' => 'world'];
  $responseFactory = new ResponseFactory;
  $jsonResponse = $responseFactory->json($content);

  expect($jsonResponse)->toBeInstanceOf(SymfonyResponse::class);
  expect($jsonResponse->getContent())->toBe('{"hello":"world"}');
  expect($jsonResponse->getStatusCode())->toBe(Response::HTTP_OK);
});

it('creates stream default response', function () {
  $responseFactory = new ResponseFactory;
  $streamedResponse = $responseFactory->stream(function (): void {
    echo 'hello';
  });

  expect($streamedResponse)->toBeInstanceOf(SymfonyResponse::class);
  expect($streamedResponse->getContent())->toBeFalse();
  expect($streamedResponse->getStatusCode())->toBe(Response::HTTP_OK);
});

it('creates download default response', function () {
  $temp = tempnam(sys_get_temp_dir(), 'fixture');
  $fh = fopen($temp, 'w+');
  fwrite($fh, 'writing to tempfile');
  fclose($fh);

  $responseFactory = new ResponseFactory;
  $binaryFileResponse = $responseFactory->download($temp);

  expect($binaryFileResponse)->toBeInstanceOf(SymfonyResponse::class);
  expect($binaryFileResponse->getContent())->toBeFalse();
  expect($binaryFileResponse->getStatusCode())->toBe(Response::HTTP_OK);

  unlink($temp);
});

it('creates json response from arrayable interface', function () {
  // mock one Arrayable object
  $content = $this->getMockBuilder(Arrayable::class)
    ->onlyMethods(['toArray'])
    ->getMock();
  $content->expects($this->once())
    ->method('toArray')
    ->willReturn(['hello' => 'world']);

  $responseFactory = new ResponseFactory;
  $jsonResponse = $responseFactory->json($content);

  expect($jsonResponse)->toBeInstanceOf(SymfonyResponse::class);
  expect($jsonResponse->getContent())->toBe('{"hello":"world"}');
  expect($jsonResponse->getStatusCode())->toBe(Response::HTTP_OK);
});

it('handles stream deferred callback', function () {
  $responseFactory = new ResponseFactory;
  $streamedResponse = $responseFactory->stream(function (): void {
    $this->fail();
  });

  expect($streamedResponse->getContent())->toBeFalse();
});

it('creates response with custom status and headers', function () {
  $responseFactory = new ResponseFactory;
  $response = $responseFactory->make('content', 201, ['X-Custom' => 'value']);

  expect($response->getStatusCode())->toBe(201);
  expect($response->headers->get('X-Custom'))->toBe('value');
});

it('creates json response with custom status and headers', function () {
  $responseFactory = new ResponseFactory;
  $response = $responseFactory->json(['key' => 'value'], 202, ['X-Json' => 'test']);

  expect($response->getStatusCode())->toBe(202);
  expect($response->headers->get('X-Json'))->toBe('test');
  expect($response->headers->get('Content-Type'))->toContain('application/json');
});

it('creates jsonp response', function () {
  $responseFactory = new ResponseFactory;
  $response = $responseFactory->jsonp('callback', ['key' => 'value']);

  expect($response)->toBeInstanceOf(SymfonyResponse::class);
  expect($response->getContent())->toContain('callback({"key":"value"})');
});

it('creates stream download response', function () {
  $responseFactory = new ResponseFactory;
  $response = $responseFactory->streamDownload(function () {
    echo 'test content';
  }, 'test.txt');

  expect($response)->toBeInstanceOf(SymfonyResponse::class);
  expect($response->headers->get('Content-Disposition'))->toContain('attachment');
  expect($response->headers->get('Content-Disposition'))->toContain('test.txt');
});

it('creates file response', function () {
  $temp = tempnam(sys_get_temp_dir(), 'fixture');
  file_put_contents($temp, 'test content');

  $responseFactory = new ResponseFactory;
  $response = $responseFactory->file($temp);

  expect($response)->toBeInstanceOf(SymfonyResponse::class);
  expect($response->getStatusCode())->toBe(200);

  unlink($temp);
});

it('creates download with custom name', function () {
  $temp = tempnam(sys_get_temp_dir(), 'fixture');
  file_put_contents($temp, 'test content');

  $responseFactory = new ResponseFactory;
  $response = $responseFactory->download($temp, 'custom-name.txt');

  expect($response)->toBeInstanceOf(SymfonyResponse::class);
  expect($response->headers->get('Content-Disposition'))->toContain('custom-name.txt');

  unlink($temp);
});
