<?php

namespace Kasi\Console\Scheduling;

use Kasi\Console\Command;
use Kasi\Console\Events\ScheduledBackgroundTaskFinished;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'schedule:finish')]
class ScheduleFinishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'schedule:finish {id} {code=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle the completion of a scheduled command';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     *
     * @param  \Kasi\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function handle(Schedule $schedule)
    {
        (new Collection($schedule->events()))->filter(function ($value) {
            return $value->mutexName() == $this->argument('id');
        })->each(function ($event) {
            $event->finish($this->kasi, $this->argument('code'));

            $this->kasi->make(Dispatcher::class)->dispatch(new ScheduledBackgroundTaskFinished($event));
        });
    }
}
