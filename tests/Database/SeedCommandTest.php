<?php

namespace Kasi\Tests\Database;

use Kasi\Console\OutputStyle;
use Kasi\Console\View\Components\Factory;
use Kasi\Container\Container;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Database\ConnectionResolverInterface;
use Kasi\Database\Console\Seeds\SeedCommand;
use Kasi\Database\Console\Seeds\WithoutModelEvents;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Seeder;
use Kasi\Events\NullDispatcher;
use Kasi\Testing\Assert;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class SeedCommandTest extends TestCase
{
    public function testHandle()
    {
        $input = new ArrayInput(['--force' => true, '--database' => 'sqlite']);
        $output = new NullOutput;
        $outputStyle = new OutputStyle($input, $output);

        $seeder = m::mock(Seeder::class);
        $seeder->shouldReceive('setContainer')->once()->andReturnSelf();
        $seeder->shouldReceive('setCommand')->once()->andReturnSelf();
        $seeder->shouldReceive('__invoke')->once();

        $resolver = m::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('getDefaultConnection')->once();
        $resolver->shouldReceive('setDefaultConnection')->once()->with('sqlite');

        $container = m::mock(Container::class);
        $container->shouldReceive('call');
        $container->shouldReceive('environment')->once()->andReturn('testing');
        $container->shouldReceive('runningUnitTests')->andReturn('true');
        $container->shouldReceive('make')->with('DatabaseSeeder')->andReturn($seeder);
        $container->shouldReceive('make')->with(OutputStyle::class, m::any())->andReturn(
            $outputStyle
        );
        $container->shouldReceive('make')->with(Factory::class, m::any())->andReturn(
            new Factory($outputStyle)
        );

        $command = new SeedCommand($resolver);
        $command->setKasi($container);

        // call run to set up IO, then fire manually.
        $command->run($input, $output);
        $command->handle();

        $container->shouldHaveReceived('call')->with([$command, 'handle']);
    }

    public function testWithoutModelEvents()
    {
        $input = new ArrayInput([
            '--force' => true,
            '--database' => 'sqlite',
            '--class' => UserWithoutModelEventsSeeder::class,
        ]);
        $output = new NullOutput;
        $outputStyle = new OutputStyle($input, $output);

        $instance = new UserWithoutModelEventsSeeder();

        $seeder = m::mock($instance);
        $seeder->shouldReceive('setContainer')->once()->andReturnSelf();
        $seeder->shouldReceive('setCommand')->once()->andReturnSelf();

        $resolver = m::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('getDefaultConnection')->once();
        $resolver->shouldReceive('setDefaultConnection')->once()->with('sqlite');

        $container = m::mock(Container::class);
        $container->shouldReceive('call');
        $container->shouldReceive('environment')->once()->andReturn('testing');
        $container->shouldReceive('runningUnitTests')->andReturn('true');
        $container->shouldReceive('make')->with(UserWithoutModelEventsSeeder::class)->andReturn($seeder);
        $container->shouldReceive('make')->with(OutputStyle::class, m::any())->andReturn(
            $outputStyle
        );
        $container->shouldReceive('make')->with(Factory::class, m::any())->andReturn(
            new Factory($outputStyle)
        );

        $command = new SeedCommand($resolver);
        $command->setKasi($container);

        Model::setEventDispatcher($dispatcher = m::mock(Dispatcher::class));

        // call run to set up IO, then fire manually.
        $command->run($input, $output);
        $command->handle();

        Assert::assertSame($dispatcher, Model::getEventDispatcher());

        $container->shouldHaveReceived('call')->with([$command, 'handle']);
    }

    protected function tearDown(): void
    {
        Model::unsetEventDispatcher();

        m::close();
    }
}

class UserWithoutModelEventsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run()
    {
        Assert::assertInstanceOf(NullDispatcher::class, Model::getEventDispatcher());
    }
}
