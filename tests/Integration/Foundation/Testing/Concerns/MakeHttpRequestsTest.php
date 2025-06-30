<?php

namespace Kasi\Tests\Integration\Foundation\Testing\Concerns;

use Kasi\Http\Request;
use Kasi\Support\Uri;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\TestCase;

#[WithConfig('app.key', 'base64:IUHRqAQ99pZ0A1MPjbuv1D6ff3jxv0GIvS2qIW4JNU4=')]
class MakeHttpRequestsTest extends TestCase
{
    /** {@inheritDoc} */
    protected function defineWebRoutes($router)
    {
        $router->get('decode', fn (Request $request) => [
            'url' => $request->fullUrl(),
            'query' => $request->query(),
        ]);
    }

    public function test_it_can_use_uri_to_make_request()
    {
        $this->getJson(Uri::of('decode')->withQuery(['editing' => true, 'editMode' => 'create', 'search' => 'Kasi']))
            ->assertSuccessful()
            ->assertJson([
                'url' => 'http://localhost/decode?editMode=create&editing=1&search=Kasi',
                'query' => [
                    'editing' => '1',
                    'editMode' => 'create',
                    'search' => 'Kasi',
                ],
            ]);
    }
}
