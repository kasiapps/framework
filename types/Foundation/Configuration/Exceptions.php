<?php

use Kasi\Container\Container;
use Kasi\Database\Eloquent\ModelNotFoundException;
use Kasi\Foundation\Configuration\Exceptions;
use Kasi\Foundation\Exceptions\Handler;
use Symfony\Component\HttpKernel\Exception\HttpException;

$exceptions = new Exceptions(
    new Handler(
        new Container,
    ),
);

$exceptions->stopIgnoring(HttpException::class);
$exceptions->stopIgnoring([ModelNotFoundException::class]);
