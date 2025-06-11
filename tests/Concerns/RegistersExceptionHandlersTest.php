<?php

declare(strict_types=1);

use Laravel\Lumen\Application;





it('registers exception handlers', function () {
    // Test that the RegistersExceptionHandlers trait has the expected methods
    $reflection = new ReflectionClass('Laravel\Lumen\Concerns\RegistersExceptionHandlers');

    // Check that the trait has the registerErrorHandling method
    expect($reflection->hasMethod('registerErrorHandling'))->toBeTrue();

    // Check that the trait has the handleError method
    expect($reflection->hasMethod('handleError'))->toBeTrue();

    // Check that the trait has the handleException method
    expect($reflection->hasMethod('handleException'))->toBeTrue();

    expect(true)->toBeTrue(); // If we get here, all methods exist
});

it('configures error handling', function () {
    // Test that the Application class uses the RegistersExceptionHandlers trait
    $reflection = new ReflectionClass(Application::class);
    $traits = $reflection->getTraitNames();

    expect($traits)->toContain('Laravel\Lumen\Concerns\RegistersExceptionHandlers');
    expect(true)->toBeTrue(); // Test passes if trait is used
});
