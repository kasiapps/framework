<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Mockery as m;

afterEach(function () {
    m::close();
    // Restore error handlers to prevent warnings
    restore_error_handler();
    restore_exception_handler();
});

it('creates pipeline instance', function () {
    $app = new Application();
    $pipeline = new Pipeline($app);

    expect($pipeline)->toBeInstanceOf(Pipeline::class);
});

it('handles successful pipeline execution', function () {
    $app = new Application();
    $request = Request::create('/test', 'GET');

    $pipeline = new Pipeline($app);
    $pipeline->send($request);

    $middleware = [
        function ($request, $next) {
            $request->attributes->set('middleware1', 'executed');
            return $next($request);
        },
        function ($request, $next) {
            $request->attributes->set('middleware2', 'executed');
            return $next($request);
        }
    ];

    $result = $pipeline->through($middleware)->then(function ($request) {
        return new Response('Success: ' . $request->attributes->get('middleware1') . ', ' . $request->attributes->get('middleware2'));
    });

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getContent())->toBe('Success: executed, executed');
});

it('handles exception in middleware slice', function () {
    $app = new Application();
    $request = Request::create('/test', 'GET');

    // Mock exception handler
    $exceptionHandler = m::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')->once();
    $exceptionHandler->shouldReceive('render')->once()->with($request, m::type(Exception::class))->andReturn(new Response('Error handled', 500));

    $app->instance(ExceptionHandler::class, $exceptionHandler);

    $pipeline = new Pipeline($app);
    $pipeline->send($request);

    $middleware = [
        function ($request, $next) {
            throw new Exception('Middleware error');
        }
    ];

    $result = $pipeline->through($middleware)->then(function ($request) {
        return new Response('Should not reach here');
    });

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getContent())->toBe('Error handled');
    expect($result->getStatusCode())->toBe(500);
});

it('handles exception in destination closure', function () {
    $app = new Application();
    $request = Request::create('/test', 'GET');

    // Mock exception handler
    $exceptionHandler = m::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')->once();
    $exceptionHandler->shouldReceive('render')->once()->with($request, m::type(Exception::class))->andReturn(new Response('Destination error handled', 500));

    $app->instance(ExceptionHandler::class, $exceptionHandler);

    $pipeline = new Pipeline($app);
    $pipeline->send($request);

    $result = $pipeline->through([])->then(function ($request) {
        throw new Exception('Destination error');
    });

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getContent())->toBe('Destination error handled');
    expect($result->getStatusCode())->toBe(500);
});

it('rethrows exception when no exception handler bound', function () {
    $app = new Application();
    $request = Request::create('/test', 'GET');

    $pipeline = new Pipeline($app);
    $pipeline->send($request);

    $middleware = [
        function ($request, $next) {
            throw new Exception('Unhandled middleware error');
        }
    ];

    expect(function () use ($pipeline, $middleware) {
        $pipeline->through($middleware)->then(function ($request) {
            return new Response('Should not reach here');
        });
    })->toThrow(Exception::class, 'Unhandled middleware error');
});

it('rethrows exception when passable is not a Request instance', function () {
    $app = new Application();
    $notARequest = 'not a request';

    // Mock exception handler (should not be used)
    $exceptionHandler = m::mock(ExceptionHandler::class);
    $exceptionHandler->shouldNotReceive('report');
    $exceptionHandler->shouldNotReceive('render');

    $app->instance(ExceptionHandler::class, $exceptionHandler);

    $pipeline = new Pipeline($app);
    $pipeline->send($notARequest);

    $middleware = [
        function ($passable, $next) {
            throw new Exception('Error with non-request passable');
        }
    ];

    expect(function () use ($pipeline, $middleware) {
        $pipeline->through($middleware)->then(function ($passable) {
            return 'Should not reach here';
        });
    })->toThrow(Exception::class, 'Error with non-request passable');
});

it('handles exception in destination when passable is not Request', function () {
    $app = new Application();
    $notARequest = 'not a request';

    // Mock exception handler (should not be used)
    $exceptionHandler = m::mock(ExceptionHandler::class);
    $exceptionHandler->shouldNotReceive('report');
    $exceptionHandler->shouldNotReceive('render');

    $app->instance(ExceptionHandler::class, $exceptionHandler);

    $pipeline = new Pipeline($app);
    $pipeline->send($notARequest);

    expect(function () use ($pipeline) {
        $pipeline->through([])->then(function ($passable) {
            throw new Exception('Destination error with non-request');
        });
    })->toThrow(Exception::class, 'Destination error with non-request');
});

it('handles multiple middleware with exception in middle', function () {
    $app = new Application();
    $request = Request::create('/test', 'GET');

    // Mock exception handler
    $exceptionHandler = m::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')->once();
    $exceptionHandler->shouldReceive('render')->once()->with($request, m::type(Exception::class))->andReturn(new Response('Middle error handled', 500));

    $app->instance(ExceptionHandler::class, $exceptionHandler);

    $pipeline = new Pipeline($app);
    $pipeline->send($request);

    $middleware = [
        function ($request, $next) {
            $request->attributes->set('first', 'executed');
            return $next($request);
        },
        function ($request, $next) {
            throw new Exception('Middle middleware error');
        },
        function ($request, $next) {
            $request->attributes->set('third', 'should not execute');
            return $next($request);
        }
    ];

    $result = $pipeline->through($middleware)->then(function ($request) {
        return new Response('Should not reach destination');
    });

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getContent())->toBe('Middle error handled');
    expect($result->getStatusCode())->toBe(500);
    expect($request->attributes->get('first'))->toBe('executed');
    expect($request->attributes->has('third'))->toBeFalse();
});

it('works with complex middleware stack', function () {
    $app = new Application();
    $request = Request::create('/test', 'GET');

    $pipeline = new Pipeline($app);
    $pipeline->send($request);

    $middleware = [
        function ($request, $next) {
            $request->attributes->set('order', ($request->attributes->get('order', '') . '1'));
            return $next($request);
        },
        function ($request, $next) {
            $request->attributes->set('order', ($request->attributes->get('order', '') . '2'));
            return $next($request);
        }
    ];

    $result = $pipeline->through($middleware)->then(function ($request) {
        $request->attributes->set('order', ($request->attributes->get('order', '') . 'destination'));
        return new Response('Order: ' . $request->attributes->get('order'));
    });

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getContent())->toBe('Order: 12destination');
});
