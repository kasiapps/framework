<?php

namespace Kasi\Queue\Console;

use Kasi\Console\Command;
use Kasi\Contracts\Cache\Repository as Cache;
use Kasi\Contracts\Queue\Job;
use Kasi\Queue\Events\JobFailed;
use Kasi\Queue\Events\JobProcessed;
use Kasi\Queue\Events\JobProcessing;
use Kasi\Queue\Events\JobReleasedAfterException;
use Kasi\Queue\Worker;
use Kasi\Queue\WorkerOptions;
use Kasi\Support\Carbon;
use Kasi\Support\InteractsWithTime;
use Kasi\Support\Stringable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Terminal;
use Throwable;

use function Termwind\terminal;

#[AsCommand(name: 'queue:work')]
class WorkCommand extends Command
{
    use InteractsWithTime;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:work
                            {connection? : The name of the queue connection to work}
                            {--name=default : The name of the worker}
                            {--queue= : The names of the queues to work}
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--delay=0 : The number of seconds to delay failed jobs (Deprecated)}
                            {--backoff=0 : The number of seconds to wait before retrying a job that encountered an uncaught exception}
                            {--max-jobs=0 : The number of jobs to process before stopping}
                            {--max-time=0 : The maximum number of seconds the worker should run}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--rest=0 : Number of seconds to rest between jobs}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=1 : Number of times to attempt a job before logging it failed}
                            {--json : Output the queue worker information as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start processing jobs on the queue as a daemon';

    /**
     * The queue worker instance.
     *
     * @var \Kasi\Queue\Worker
     */
    protected $worker;

    /**
     * The cache store implementation.
     *
     * @var \Kasi\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Holds the start time of the last processed job, if any.
     *
     * @var float|null
     */
    protected $latestStartedAt;

    /**
     * Indicates if the worker's event listeners have been registered.
     *
     * @var bool
     */
    private static $hasRegisteredListeners = false;

    /**
     * Create a new queue work command.
     *
     * @param  \Kasi\Queue\Worker  $worker
     * @param  \Kasi\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function __construct(Worker $worker, Cache $cache)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->worker = $worker;
    }

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        if ($this->downForMaintenance() && $this->option('once')) {
            return $this->worker->sleep($this->option('sleep'));
        }

        // We'll listen to the processed and failed events so we can write information
        // to the console as jobs are processed, which will let the developer watch
        // which jobs are coming through a queue and be informed on its progress.
        $this->listenForEvents();

        $connection = $this->argument('connection')
                        ?: $this->kasi['config']['queue.default'];

        // We need to get the right queue for the connection which is set in the queue
        // configuration file for the application. We will pull it based on the set
        // connection being run for the queue operation currently being executed.
        $queue = $this->getQueue($connection);

        if (! $this->outputUsingJson() && Terminal::hasSttyAvailable()) {
            $this->components->info(
                sprintf('Processing jobs from the [%s] %s.', $queue, (new Stringable('queue'))->plural(explode(',', $queue)))
            );
        }

        return $this->runWorker(
            $connection, $queue
        );
    }

    /**
     * Run the worker instance.
     *
     * @param  string  $connection
     * @param  string  $queue
     * @return int|null
     */
    protected function runWorker($connection, $queue)
    {
        return $this->worker
            ->setName($this->option('name'))
            ->setCache($this->cache)
            ->{$this->option('once') ? 'runNextJob' : 'daemon'}(
                $connection, $queue, $this->gatherWorkerOptions()
            );
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Kasi\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->option('name'),
            max($this->option('backoff'), $this->option('delay')),
            $this->option('memory'),
            $this->option('timeout'),
            $this->option('sleep'),
            $this->option('tries'),
            $this->option('force'),
            $this->option('stop-when-empty'),
            $this->option('max-jobs'),
            $this->option('max-time'),
            $this->option('rest')
        );
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents()
    {
        if (static::$hasRegisteredListeners) {
            return;
        }

        $this->kasi['events']->listen(JobProcessing::class, function ($event) {
            $this->writeOutput($event->job, 'starting');
        });

        $this->kasi['events']->listen(JobProcessed::class, function ($event) {
            $this->writeOutput($event->job, 'success');
        });

        $this->kasi['events']->listen(JobReleasedAfterException::class, function ($event) {
            $this->writeOutput($event->job, 'released_after_exception');
        });

        $this->kasi['events']->listen(JobFailed::class, function ($event) {
            $this->writeOutput($event->job, 'failed', $event->exception);

            $this->logFailedJob($event);
        });

        static::$hasRegisteredListeners = true;
    }

    /**
     * Write the status output for the queue worker for JSON or TTY.
     *
     * @param  Job  $job
     * @param  string  $status
     * @param  Throwable|null  $exception
     * @return void
     */
    protected function writeOutput(Job $job, $status, ?Throwable $exception = null)
    {
        $this->outputUsingJson()
            ? $this->writeOutputAsJson($job, $status, $exception)
            : $this->writeOutputForCli($job, $status);
    }

    /**
     * Write the status output for the queue worker.
     *
     * @param  \Kasi\Contracts\Queue\Job  $job
     * @param  string  $status
     * @return void
     */
    protected function writeOutputForCli(Job $job, $status)
    {
        $this->output->write(sprintf(
            '  <fg=gray>%s</> %s%s',
            $this->now()->format('Y-m-d H:i:s'),
            $job->resolveName(),
            $this->output->isVerbose()
                ? sprintf(' <fg=gray>%s</>', $job->getJobId())
                : ''
        ));

        if ($status == 'starting') {
            $this->latestStartedAt = microtime(true);

            $dots = max(terminal()->width() - mb_strlen($job->resolveName()) - (
                $this->output->isVerbose() ? (mb_strlen($job->getJobId()) + 1) : 0
            ) - 33, 0);

            $this->output->write(' '.str_repeat('<fg=gray>.</>', $dots));

            return $this->output->writeln(' <fg=yellow;options=bold>RUNNING</>');
        }

        $runTime = $this->runTimeForHumans($this->latestStartedAt);

        $dots = max(terminal()->width() - mb_strlen($job->resolveName()) - (
            $this->output->isVerbose() ? (mb_strlen($job->getJobId()) + 1) : 0
        ) - mb_strlen($runTime) - 31, 0);

        $this->output->write(' '.str_repeat('<fg=gray>.</>', $dots));
        $this->output->write(" <fg=gray>$runTime</>");

        $this->output->writeln(match ($status) {
            'success' => ' <fg=green;options=bold>DONE</>',
            'released_after_exception' => ' <fg=yellow;options=bold>FAIL</>',
            default => ' <fg=red;options=bold>FAIL</>',
        });
    }

    /**
     * Write the status output for the queue worker in JSON format.
     *
     * @param  \Kasi\Contracts\Queue\Job  $job
     * @param  string  $status
     * @param  Throwable|null  $exception
     * @return void
     */
    protected function writeOutputAsJson(Job $job, $status, ?Throwable $exception = null)
    {
        $log = array_filter([
            'level' => $status === 'starting' || $status === 'success' ? 'info' : 'warning',
            'id' => $job->getJobId(),
            'uuid' => $job->uuid(),
            'connection' => $job->getConnectionName(),
            'queue' => $job->getQueue(),
            'job' => $job->resolveName(),
            'status' => $status,
            'result' => match (true) {
                $job->isDeleted() => 'deleted',
                $job->isReleased() => 'released',
                $job->hasFailed() => 'failed',
                default => '',
            },
            'attempts' => $job->attempts(),
            'exception' => $exception ? $exception::class : '',
            'message' => $exception?->getMessage(),
            'timestamp' => $this->now()->format('Y-m-d\TH:i:s.uP'),
        ]);

        if ($status === 'starting') {
            $this->latestStartedAt = microtime(true);
        } else {
            $log['duration'] = round(microtime(true) - $this->latestStartedAt, 6);
        }

        $this->output->writeln(json_encode($log));
    }

    /**
     * Get the current date / time.
     *
     * @return \Kasi\Support\Carbon
     */
    protected function now()
    {
        $queueTimezone = $this->kasi['config']->get('queue.output_timezone');

        if ($queueTimezone &&
            $queueTimezone !== $this->kasi['config']->get('app.timezone')) {
            return Carbon::now()->setTimezone($queueTimezone);
        }

        return Carbon::now();
    }

    /**
     * Store a failed job event.
     *
     * @param  \Kasi\Queue\Events\JobFailed  $event
     * @return void
     */
    protected function logFailedJob(JobFailed $event)
    {
        $this->kasi['queue.failer']->log(
            $event->connectionName,
            $event->job->getQueue(),
            $event->job->getRawBody(),
            $event->exception
        );
    }

    /**
     * Get the queue name for the worker.
     *
     * @param  string  $connection
     * @return string
     */
    protected function getQueue($connection)
    {
        return $this->option('queue') ?: $this->kasi['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }

    /**
     * Determine if the worker should run in maintenance mode.
     *
     * @return bool
     */
    protected function downForMaintenance()
    {
        return $this->option('force') ? false : $this->kasi->isDownForMaintenance();
    }

    /**
     * Determine if the worker should output using JSON.
     *
     * @return bool
     */
    protected function outputUsingJson()
    {
        if (! $this->hasOption('json')) {
            return false;
        }

        return $this->option('json');
    }

    /**
     * Reset static variables.
     *
     * @return void
     */
    public static function flushState()
    {
        static::$hasRegisteredListeners = false;
    }
}
