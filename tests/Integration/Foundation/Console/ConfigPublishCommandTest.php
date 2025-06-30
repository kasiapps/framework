<?php

namespace Kasi\Tests\Integration\Foundation\Console;

use Kasi\Filesystem\Filesystem;
use Kasi\Foundation\Bootstrap\LoadConfiguration;
use Kasi\Support\ServiceProvider;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;
use Orchestra\Testbench\TestCase;

use function Orchestra\Testbench\package_path;

class ConfigPublishCommandTest extends TestCase
{
    use InteractsWithPublishedFiles;

    protected array $files = [
        'config-stubs/*.php',
    ];

    #[\Override]
    protected function setUp(): void
    {
        $files = new Filesystem();

        $this->afterApplicationCreated(function () use ($files) {
            $files->ensureDirectoryExists($this->app->basePath('config-stubs'));
        });

        $this->beforeApplicationDestroyed(function () use ($files) {
            $files->deleteDirectory($this->app->basePath('config-stubs'));
        });

        parent::setUp();
    }

    #[\Override]
    protected function resolveApplicationConfiguration($app)
    {
        $app->instance(LoadConfiguration::class, new LoadConfiguration());

        $app->useConfigPath($app->basePath('config-stubs'));

        $app->dontMergeFrameworkConfiguration();

        parent::resolveApplicationConfiguration($app);
    }

    public function testItCanPublishConfigFilesWhenConfiguredWithDontMergeFrameworkConfiguration()
    {
        $this->artisan('config:publish', ['--all' => true])->assertOk();

        foreach ([
            'app', 'auth', 'broadcasting', 'cache', 'cors',
            'database', 'filesystems', 'hashing', 'logging',
            'mail', 'queue', 'services', 'session', 'view',
        ] as $file) {
            $this->assertFilenameExists("config-stubs/{$file}.php");
            $this->assertStringContainsString(
                file_get_contents(package_path(['config', "{$file}.php"])), file_get_contents(config_path("{$file}.php"))
            );
        }

        $this->assertSame(config('app.providers'), ServiceProvider::defaultProviders()->toArray());
    }
}
