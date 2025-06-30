<?php

namespace Kasi\Tests\Integration\Foundation\Fixtures\Providers;

use Kasi\Console\Application;
use Kasi\Support\ServiceProvider;
use Kasi\Tests\Integration\Foundation\Fixtures\Console\ThrowExceptionCommand;

class ThrowExceptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Application::starting(function ($artisan) {
            $artisan->add(new ThrowExceptionCommand);
        });
    }
}
