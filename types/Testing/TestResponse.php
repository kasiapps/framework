<?php

use Kasi\Http\JsonResponse;
use Kasi\Http\RedirectResponse;
use Kasi\Http\Response;
use Kasi\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function PHPStan\Testing\assertType;

$response = TestResponse::fromBaseResponse(response('Kasi', 200));
assertType(Response::class, $response->baseResponse);

$response = TestResponse::fromBaseResponse(response()->redirectTo(''));
assertType(RedirectResponse::class, $response->baseResponse);

$response = TestResponse::fromBaseResponse(response()->download(''));
assertType(BinaryFileResponse::class, $response->baseResponse);

$response = TestResponse::fromBaseResponse(response()->json());
assertType(JsonResponse::class, $response->baseResponse);

$response = TestResponse::fromBaseResponse(response()->streamDownload(fn () => 1));
assertType(StreamedResponse::class, $response->baseResponse);
