<?php

namespace Kasi\Tests\Integration\Redis;

use Kasi\Redis\Connections\PredisConnection;
use Kasi\Redis\Events\CommandExecuted;
use Kasi\Support\Facades\Event;
use Mockery as m;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\TestCase;
use Predis\Client;
use Predis\Command\Argument\Search\SearchArguments;

#[WithConfig('database.redis.client', 'predis')]
class PredisConnectionTest extends TestCase
{
    public function testPredisCanEmitEventWithArrayableArgumentObject()
    {
        if (! class_exists(SearchArguments::class)) {
            return $this->markTestSkipped('Skipped tests on predis/predis dependency without '.SearchArguments::class);
        }

        $event = Event::fake();

        $command = 'ftSearch';
        $parameters = ['test', '*', (new SearchArguments())->dialect('3')->withScores()];

        $predis = new PredisConnection($client = m::mock(Client::class));
        $predis->setEventDispatcher($event);

        $client->shouldReceive($command)->with(...$parameters)->andReturnTrue();

        $this->assertTrue($predis->command($command, $parameters));

        $event->assertDispatched(function (CommandExecuted $event) use ($command) {
            return $event->connection instanceof PredisConnection
                && $event->command === $command
                && $event->parameters === ['test', '*', ['DIALECT', '3', 'WITHSCORES']];
        });
    }
}
