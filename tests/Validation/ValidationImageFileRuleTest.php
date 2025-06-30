<?php

namespace Kasi\Tests\Validation;

use Kasi\Container\Container;
use Kasi\Http\UploadedFile;
use Kasi\Support\Arr;
use Kasi\Support\Facades\Facade;
use Kasi\Translation\ArrayLoader;
use Kasi\Translation\Translator;
use Kasi\Validation\Rule;
use Kasi\Validation\Rules\File;
use Kasi\Validation\ValidationServiceProvider;
use Kasi\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationImageFileRuleTest extends TestCase
{
    public function testDimensions()
    {
        $this->fails(
            File::image()->dimensions(Rule::dimensions()->width(100)->height(100)),
            UploadedFile::fake()->image('foo.png', 101, 101),
            ['validation.dimensions'],
        );

        $this->passes(
            File::image()->dimensions(Rule::dimensions()->width(100)->height(100)),
            UploadedFile::fake()->image('foo.png', 100, 100),
        );
    }

    public function testDimensionsWithCustomImageSizeMethod()
    {
        $this->fails(
            File::image()->dimensions(Rule::dimensions()->width(100)->height(100)),
            new UploadedFileWithCustomImageSizeMethod(stream_get_meta_data($tmpFile = tmpfile())['uri'], 'foo.png'),
            ['validation.dimensions'],
        );

        $this->passes(
            File::image()->dimensions(Rule::dimensions()->width(200)->height(200)),
            new UploadedFileWithCustomImageSizeMethod(stream_get_meta_data($tmpFile = tmpfile())['uri'], 'foo.png'),
        );
    }

    public function testDimentionWithTheRatioMethod()
    {
        $this->fails(
            File::image()->dimensions(Rule::dimensions()->ratio(1)),
            UploadedFile::fake()->image('foo.png', 105, 100),
            ['validation.dimensions'],
        );

        $this->passes(
            File::image()->dimensions(Rule::dimensions()->ratio(1)),
            UploadedFile::fake()->image('foo.png', 100, 100),
        );
    }

    public function testDimentionWithTheMinRatioMethod()
    {
        $this->fails(
            File::image()->dimensions(Rule::dimensions()->minRatio(1 / 2)),
            UploadedFile::fake()->image('foo.png', 100, 100),
            ['validation.dimensions'],
        );

        $this->passes(
            File::image()->dimensions(Rule::dimensions()->minRatio(1 / 2)),
            UploadedFile::fake()->image('foo.png', 100, 200),
        );
    }

    public function testDimentionWithTheMaxRatioMethod()
    {
        $this->fails(
            File::image()->dimensions(Rule::dimensions()->maxRatio(1 / 2)),
            UploadedFile::fake()->image('foo.png', 100, 300),
            ['validation.dimensions'],
        );

        $this->passes(
            File::image()->dimensions(Rule::dimensions()->maxRatio(1 / 2)),
            UploadedFile::fake()->image('foo.png', 100, 100),
        );
    }

    public function testDimentionWithTheRatioBetweenMethod()
    {
        $this->fails(
            File::image()->dimensions(Rule::dimensions()->ratioBetween(1 / 2, 1 / 3)),
            UploadedFile::fake()->image('foo.png', 100, 100),
            ['validation.dimensions'],
        );

        $this->passes(
            File::image()->dimensions(Rule::dimensions()->ratioBetween(1 / 2, 1 / 3)),
            UploadedFile::fake()->image('foo.png', 100, 200),
        );
    }

    protected function fails($rule, $values, $messages)
    {
        $this->assertValidationRules($rule, $values, false, $messages);
    }

    protected function assertValidationRules($rule, $values, $result, $messages)
    {
        $values = Arr::wrap($values);

        foreach ($values as $value) {
            $v = new Validator(
                resolve('translator'),
                ['my_file' => $value],
                ['my_file' => is_object($rule) ? clone $rule : $rule]
            );

            $this->assertSame($result, $v->passes());

            $this->assertSame(
                $result ? [] : ['my_file' => $messages],
                $v->messages()->toArray()
            );
        }
    }

    protected function passes($rule, $values)
    {
        $this->assertValidationRules($rule, $values, true, []);
    }

    protected function setUp(): void
    {
        $container = Container::getInstance();

        $container->bind('translator', function () {
            return new Translator(
                new ArrayLoader, 'en'
            );
        });

        Facade::setFacadeApplication($container);

        (new ValidationServiceProvider($container))->register();
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);

        Facade::clearResolvedInstances();

        Facade::setFacadeApplication(null);
    }
}

class UploadedFileWithCustomImageSizeMethod extends UploadedFile
{
    public function isValid(): bool
    {
        return true;
    }

    public function guessExtension(): string
    {
        return 'png';
    }

    public function dimensions()
    {
        return [200, 200];
    }
}
