<?php

namespace Kasi\Tests\Integration\Database\Queue\Fixtures;

use Kasi\Bus\Queueable;
use Kasi\Contracts\Queue\ShouldQueue;
use Kasi\Queue\InteractsWithQueue;
use Kasi\Support\Facades\DB;

class TimeOutNonBatchableJobWithTransaction implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 1;
    public int $timeout = 2;

    public function handle(): void
    {
        DB::transaction(fn () => sleep(20));
    }
}
