<?php

namespace Kasi\Tests\Foundation;

use Kasi\Foundation\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FoundationApplicationBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        unset($_ENV['APP_BASE_PATH']);

        unset($_ENV['KASI_STORAGE_PATH'], $_SERVER['KASI_STORAGE_PATH']);

        parent::tearDown();
    }

    public function testBaseDirectoryWithArg()
    {
        $_ENV['APP_BASE_PATH'] = __DIR__.'/as-env';

        $app = Application::configure(__DIR__.'/as-arg')->create();

        $this->assertSame(__DIR__.'/as-arg', $app->basePath());
    }

    public function testBaseDirectoryWithEnv()
    {
        $_ENV['APP_BASE_PATH'] = __DIR__.'/as-env';

        $app = Application::configure()->create();

        $this->assertSame(__DIR__.'/as-env', $app->basePath());
    }

    public function testBaseDirectoryWithComposer()
    {
        $app = Application::configure()->create();

        $this->assertSame(dirname(__DIR__, 2), $app->basePath());
    }

    public function testStoragePathWithGlobalEnvVariable()
    {
        $_ENV['KASI_STORAGE_PATH'] = __DIR__.'/env-storage';

        $app = Application::configure()->create();

        $this->assertSame(__DIR__.'/env-storage', $app->storagePath());
    }

    public function testStoragePathWithGlobalServerVariable()
    {
        $_SERVER['KASI_STORAGE_PATH'] = __DIR__.'/server-storage';

        $app = Application::configure()->create();

        $this->assertSame(__DIR__.'/server-storage', $app->storagePath());
    }

    public function testStoragePathPrefersEnvVariable()
    {
        $_ENV['KASI_STORAGE_PATH'] = __DIR__.'/env-storage';
        $_SERVER['KASI_STORAGE_PATH'] = __DIR__.'/server-storage';

        $app = Application::configure()->create();

        $this->assertSame(__DIR__.'/env-storage', $app->storagePath());
    }

    public function testStoragePathBasedOnBasePath()
    {
        $app = Application::configure()->create();
        $this->assertSame($app->basePath().DIRECTORY_SEPARATOR.'storage', $app->storagePath());
    }

    public function testStoragePathCanBeCustomized()
    {
        $_ENV['KASI_STORAGE_PATH'] = __DIR__.'/env-storage';

        $app = Application::configure()->create();
        $app->useStoragePath(__DIR__.'/custom-storage');

        $this->assertSame(__DIR__.'/custom-storage', $app->storagePath());
    }
}
