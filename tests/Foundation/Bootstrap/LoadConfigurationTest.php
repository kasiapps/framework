<?php

namespace Kasi\Tests\Foundation\Bootstrap;

use Kasi\Foundation\Application;
use Kasi\Foundation\Bootstrap\LoadConfiguration;
use PHPUnit\Framework\TestCase;

class LoadConfigurationTest extends TestCase
{
    public function testLoadsBaseConfiguration()
    {
        $app = new Application();

        (new LoadConfiguration())->bootstrap($app);

        $this->assertSame('Kasi', $app['config']['app.name']);
    }

    public function testDontLoadBaseConfiguration()
    {
        $app = new Application();
        $app->dontMergeFrameworkConfiguration();

        (new LoadConfiguration())->bootstrap($app);

        $this->assertNull($app['config']['app.name']);
    }

    public function testLoadsConfigurationInIsolation()
    {
        $app = new Application(__DIR__.'/../fixtures');
        $app->useConfigPath(__DIR__.'/../fixtures/config');

        (new LoadConfiguration())->bootstrap($app);

        $this->assertNull($app['config']['bar.foo']);
        $this->assertSame('bar', $app['config']['custom.foo']);
    }
}
