<?php

namespace Kasi\Tests\Queue;

use Kasi\Bus\BatchRepository;
use Kasi\Bus\DatabaseBatchRepository;
use Kasi\Foundation\Application;
use Kasi\Queue\Console\PruneBatchesCommand;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PruneBatchesCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testAllowPruningAllUnfinishedBatches()
    {
        $container = new Application;
        $container->instance(BatchRepository::class, $repo = m::spy(DatabaseBatchRepository::class));

        $command = new PruneBatchesCommand;
        $command->setKasi($container);

        $command->run(new ArrayInput(['--unfinished' => 0]), new NullOutput());

        $repo->shouldHaveReceived('pruneUnfinished')->once();
    }

    public function testAllowPruningAllCancelledBatches()
    {
        $container = new Application;
        $container->instance(BatchRepository::class, $repo = m::spy(DatabaseBatchRepository::class));

        $command = new PruneBatchesCommand;
        $command->setKasi($container);

        $command->run(new ArrayInput(['--cancelled' => 0]), new NullOutput());

        $repo->shouldHaveReceived('pruneCancelled')->once();
    }
}
