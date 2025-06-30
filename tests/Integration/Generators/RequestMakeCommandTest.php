<?php

namespace Kasi\Tests\Integration\Generators;

class RequestMakeCommandTest extends TestCase
{
    protected $files = [
        'app/Http/Requests/FooRequest.php',
    ];

    public function testItCanGenerateRequestFile()
    {
        $this->artisan('make:request', ['name' => 'FooRequest'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Http\Requests;',
            'use Kasi\Foundation\Http\FormRequest;',
            'class FooRequest extends FormRequest',
        ], 'app/Http/Requests/FooRequest.php');
    }
}
