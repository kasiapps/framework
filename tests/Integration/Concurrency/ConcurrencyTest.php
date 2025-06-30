<?php

namespace Kasi\Tests\Integration\Concurrency;

use Exception;
use Kasi\Concurrency\ProcessDriver;
use Kasi\Foundation\Application;
use Kasi\Process\Factory as ProcessFactory;
use Kasi\Support\Facades\Concurrency;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

#[RequiresOperatingSystem('Linux|DAR')]
class ConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        $this->defineCacheRoutes(<<<PHP
<?php
use Kasi\Support\Facades\Concurrency;
use Kasi\Support\Facades\Route;

Route::any('/concurrency', function () {
    return Concurrency::run([
        fn () => 1 + 1,
        fn () => 2 + 2,
    ]);
});
PHP);

        parent::setUp();
    }

    public function testWorkCanBeDistributed()
    {
        $response = $this->get('concurrency')
            ->assertOk();

        [$first, $second] = $response->original;

        $this->assertEquals(2, $first);
        $this->assertEquals(4, $second);
    }

    public function testRunHandlerProcessErrorCode()
    {
        $this->expectException(Exception::class);
        $app = new Application(__DIR__);
        $processDriver = new ProcessDriver($app->make(ProcessFactory::class));
        $processDriver->run([
            fn () => exit(1),
        ]);
    }

    public function testRunHandlerProcessErrorWithDefaultExceptionWithoutParam()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This is a different exception');

        Concurrency::run([
            fn () => throw new Exception(
                'This is a different exception',
            ),
        ]);
    }

    public function testRunHandlerProcessErrorWithCustomExceptionWithoutParam()
    {
        $this->expectException(ExceptionWithoutParam::class);
        $this->expectExceptionMessage('Test');
        Concurrency::run([
            fn () => throw new ExceptionWithoutParam('Test'),
        ]);
    }

    public function testRunHandlerProcessErrorWithCustomExceptionWithParam()
    {
        $this->expectException(ExceptionWithParam::class);
        $this->expectExceptionMessage('API request to https://api.example.com failed with status 400 Bad Request');
        Concurrency::run([
            fn () => throw new ExceptionWithParam(
                'https://api.example.com',
                400,
                'Bad Request',
                'Invalid payload'
            ),
        ]);
    }
}

class ExceptionWithoutParam extends Exception
{
}

class ExceptionWithParam extends Exception
{
    public function __construct(
        public string $uri,
        public int $statusCode,
        public string $reason,
        public string|array $responseBody = '',
    ) {
        parent::__construct("API request to {$uri} failed with status $statusCode $reason");
    }
}
