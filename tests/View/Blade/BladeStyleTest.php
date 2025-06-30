<?php

namespace Kasi\Tests\View\Blade;

class BladeStyleTest extends AbstractBladeTestCase
{
    public function testStylesAreConditionallyCompiledFromArray()
    {
        $string = "<span @style(['font-weight: bold', 'text-decoration: underline', 'color: red' => true, 'margin-top: 10px' => false])></span>";
        $expected = "<span style=\"<?php echo \Kasi\Support\Arr::toCssStyles(['font-weight: bold', 'text-decoration: underline', 'color: red' => true, 'margin-top: 10px' => false]) ?>\"></span>";

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
