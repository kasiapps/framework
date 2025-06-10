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
