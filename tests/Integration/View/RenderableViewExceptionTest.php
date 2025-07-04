<?php

namespace Kasi\Tests\Integration\View;

use Exception;
use Kasi\Http\Response;
use Kasi\Support\Facades\Route;
use Kasi\Support\Facades\View;
use Orchestra\Testbench\TestCase;

class RenderableViewExceptionTest extends TestCase
{
    public function testRenderMethodOfExceptionThrownInViewGetsHandled()
    {
        Route::get('/', function () {
            return View::make('renderable-exception');
        });

        $response = $this->get('/');

        $response->assertSee('This is a renderable exception.');
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('view.paths', [__DIR__.'/templates']);
    }
}

class RenderableException extends Exception
{
    public function render($request)
    {
        return new Response('This is a renderable exception.');
    }
}
