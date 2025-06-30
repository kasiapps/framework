<?php

namespace Kasi\Tests\Integration\Queue\Fixtures\Jobs;

use Kasi\Contracts\Queue\ShouldQueue;
use Kasi\Foundation\Auth\User;
use Kasi\Foundation\Queue\Queueable;

class DeleteUser implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {
        log($user);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->delete();
    }
}
