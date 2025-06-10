<?php

declare(strict_types=1);

namespace Laravel\Lumen\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use Laravel\Lumen\Exceptions\Handler;
use RuntimeException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Kernel implements KernelContract
{
  /**
   * The Symfony event dispatcher implementation.
   *
   * @var EventDispatcherInterface|null
   */
  protected $symfonyDispatcher;

  /**
   * The Artisan application instance.
   *
   * @var \Illuminate\Console\Application
   */
  protected $artisan;

  /**
   * Indicates if facade aliases are enabled for the console.
   *
   * @var bool
   */
  protected $aliases = true;

  /**
   * The Artisan commands provided by the application.
   *
   * @var array
   */
  protected $commands = [];

  /**
   * Create a new console kernel instance.
   *
   * @return void
   */
  public function __construct(/**
   * The application implementation.
   */
    protected \Laravel\Lumen\Application $app)
  {
    if ($this->app->runningInConsole()) {
      $this->setRequestForConsole($this->app);
    } else {
      $this->rerouteSymfonyCommandEvents();
    }

    $this->app->prepareForConsoleCommand($this->aliases);
    $this->defineConsoleSchedule();
  }

  /**
   * Set the request instance for URL generation.
   *
   * @param  \Illuminate\Contracts\Foundation\Application  $application
   * @return void
   */
  protected function setRequestForConsole(Application $application)
  {
    $uri = $application->make('config')->get('app.url', 'http://localhost');

    $components = parse_url((string) $uri);

    $server = $_SERVER;

    if (isset($components['path'])) {
      $server = array_merge($server, [
        'SCRIPT_FILENAME' => $components['path'],
        'SCRIPT_NAME' => $components['path'],
      ]);
    }

    $application->instance('request', Request::create(
      $uri, 'GET', [], [], [], $server
    ));
  }

  /**
   * Re-route the Symfony command events to their Laravel counterparts.
   *
   * @internal
   *
   * @return $this
   */
  public function rerouteSymfonyCommandEvents(): static
  {
    if (is_null($this->symfonyDispatcher)) {
      $this->symfonyDispatcher = new EventDispatcher;

      $this->symfonyDispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $consoleCommandEvent): void {
        $this->app[Dispatcher::class]->dispatch(
          new CommandStarting($consoleCommandEvent->getCommand()->getName(), $consoleCommandEvent->getInput(), $consoleCommandEvent->getOutput())
        );
      });

      $this->symfonyDispatcher->addListener(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $consoleTerminateEvent): void {
        $this->app[Dispatcher::class]->dispatch(
          new CommandFinished($consoleTerminateEvent->getCommand()->getName(), $consoleTerminateEvent->getInput(), $consoleTerminateEvent->getOutput(), $consoleTerminateEvent->getExitCode())
        );
      });
    }

    return $this;
  }

  /**
   * Define the application's command schedule.
   *
   * @return void
   */
  protected function defineConsoleSchedule()
  {
    $this->app->instance(
      Schedule::class, $schedule = new Schedule
    );

    $this->schedule($schedule);
  }

  /**
   * Run the console application.
   *
   * @param  InputInterface  $input
   * @param  OutputInterface  $output
   * @return int
   */
  public function handle($input, $output = null)
  {
    try {
      $this->app->boot();

      $status = $this->getArtisan()->run($input, $output);
    } catch (Throwable $e) {
      $this->reportException($e);

      $this->renderException($output, $e);

      $status = 1;
    }

    $this->terminate($input, $status);

    return $status;
  }

  /**
   * Bootstrap the application for artisan commands.
   */
  public function bootstrap(): void
  {
    //
  }

  /**
   * Terminate the application.
   *
   * @param  InputInterface  $input
   * @param  int  $status
   */
  public function terminate($input, $status): void
  {
    $this->app->terminate();
  }

  /**
   * Define the application's command schedule.
   *
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    //
  }

  /**
   * Run an Artisan console command by name.
   *
   * @param  string  $command
   * @return int
   */
  public function call($command, array $parameters = [], $outputBuffer = null)
  {
    return $this->getArtisan()->call($command, $parameters, $outputBuffer);
  }

  /**
   * Queue the given console command.
   *
   * @param  string  $command
   * @return void
   */
  public function queue($command, array $parameters = [])
  {
    throw new RuntimeException('Queueing Artisan commands is not supported by Lumen.');
  }

  /**
   * Get all of the commands registered with the console.
   *
   * @return array
   */
  public function all()
  {
    return $this->getArtisan()->all();
  }

  /**
   * Get the output for the last run command.
   *
   * @return string
   */
  public function output()
  {
    return $this->getArtisan()->output();
  }

  /**
   * Get the Artisan application instance.
   *
   * @return \Illuminate\Console\Application
   */
  protected function getArtisan()
  {
    if (is_null($this->artisan)) {
      $this->artisan = (new Artisan($this->app, $this->app->make('events'), $this->app->version()))
        ->resolveCommands($this->getCommands())
        ->setContainerCommandLoader();

      if ($this->symfonyDispatcher instanceof EventDispatcher) {
        $this->artisan->setDispatcher($this->symfonyDispatcher);
        $this->artisan->setSignalsToDispatchEvent();
      }
    }

    return $this->artisan;
  }

  /**
   * Get the commands to add to the application.
   */
  protected function getCommands(): array
  {
    return array_merge($this->commands, [
      ScheduleRunCommand::class,
    ]);
  }

  /**
   * Report the exception to the exception handler.
   *
   * @return void
   */
  protected function reportException(Throwable $throwable)
  {
    $this->resolveExceptionHandler()->report($throwable);
  }

  /**
   * Report the exception to the exception handler.
   *
   * @param  OutputInterface  $output
   * @return void
   */
  protected function renderException($output, Throwable $throwable)
  {
    $this->resolveExceptionHandler()->renderForConsole($output, $throwable);
  }

  /**
   * Get the exception handler from the container.
   *
   * @return ExceptionHandler
   */
  protected function resolveExceptionHandler()
  {
    if ($this->app->bound(ExceptionHandler::class)) {
      return $this->app->make(ExceptionHandler::class);
    }

    return $this->app->make(Handler::class);
  }
}
