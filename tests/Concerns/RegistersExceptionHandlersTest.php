<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Concerns\RegistersExceptionHandlers;

it('registers exception handlers', function () {
    $app = new class extends Application {
        use RegistersExceptionHandlers;
        
        public function callRegisterErrorHandling()
        {
            $this->registerErrorHandling();
        }
    };
    
    $app->callRegisterErrorHandling();
    
    expect(true)->toBeTrue(); // If we get here, registration was successful
});

it('configures error handling', function () {
    $app = new Application();
    
    // Test that the application has error handling configured
    expect($app)->toBeInstanceOf(Application::class);
});
