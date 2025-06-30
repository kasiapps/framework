<?php

declare(strict_types=1);

namespace Kasi\Tests\Console\Fixtures;

use Kasi\Contracts\Queue\ShouldQueue;

final class JobToTestWithSchedule implements ShouldQueue
{
}
