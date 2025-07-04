<?php

namespace Kasi\Tests\Foundation\Testing\Concerns;

use Kasi\Foundation\Testing\Concerns\InteractsWithViews;
use Kasi\Testing\TestComponent;
use Kasi\View\Component;
use Orchestra\Testbench\TestCase;

class InteractsWithViewsTest extends TestCase
{
    use InteractsWithViews;

    public function testBladeCorrectlyRendersString()
    {
        $string = (string) $this->blade('@if(true)test @endif');

        $this->assertSame('test ', $string);
    }

    public function testComponentCanAccessPublicProperties()
    {
        $exampleComponent = new class extends Component
        {
            public $foo = 'bar';

            public function speak()
            {
                return 'hello';
            }

            public function render()
            {
                return 'rendered content';
            }
        };

        $component = $this->component(get_class($exampleComponent));

        $this->assertSame('bar', $component->foo);
        $this->assertSame('hello', $component->speak());
        $component->assertSee('content');
    }

    public function testComponentMacroable()
    {
        TestComponent::macro('foo', fn (): string => 'bar');

        $exampleComponent = new class extends Component
        {
            public function render()
            {
                return 'rendered content';
            }
        };

        $component = $this->component(get_class($exampleComponent));

        $this->assertSame('bar', $component->foo());
    }
}
