<?php

namespace Kasi\Support\Facades;

use Kasi\Console\Scheduling\Schedule as ConsoleSchedule;

/**
 * @method static \Kasi\Console\Scheduling\CallbackEvent call(string|callable $callback, array $parameters = [])
 * @method static \Kasi\Console\Scheduling\Event command(string $command, array $parameters = [])
 * @method static \Kasi\Console\Scheduling\CallbackEvent job(object|string $job, string|null $queue = null, string|null $connection = null)
 * @method static \Kasi\Console\Scheduling\Event exec(string $command, array $parameters = [])
 * @method static void group(\Closure $events)
 * @method static string compileArrayInput(string|int $key, array $value)
 * @method static bool serverShouldRun(\Kasi\Console\Scheduling\Event $event, \DateTimeInterface $time)
 * @method static \Kasi\Support\Collection dueEvents(\Kasi\Contracts\Foundation\Application $app)
 * @method static \Kasi\Console\Scheduling\Event[] events()
 * @method static \Kasi\Console\Scheduling\Schedule useCache(string $store)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes withoutOverlapping(int $expiresAt = 1440)
 * @method static void mergeAttributes(\Kasi\Console\Scheduling\Event $event)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes user(string $user)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes environments(array|mixed $environments)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes evenInMaintenanceMode()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes onOneServer()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes runInBackground()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes when(\Closure|bool $callback)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes skip(\Closure|bool $callback)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes name(string $description)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes description(string $description)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes cron(string $expression)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes between(string $startTime, string $endTime)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes unlessBetween(string $startTime, string $endTime)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everySecond()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyTwoSeconds()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyFiveSeconds()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyTenSeconds()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyFifteenSeconds()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyTwentySeconds()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyThirtySeconds()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyMinute()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyTwoMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyThreeMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyFourMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyFiveMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyTenMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyFifteenMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyThirtyMinutes()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes hourly()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes hourlyAt(array|string|int|int[] $offset)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyOddHour(array|string|int $offset = 0)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyTwoHours(array|string|int $offset = 0)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyThreeHours(array|string|int $offset = 0)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everyFourHours(array|string|int $offset = 0)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes everySixHours(array|string|int $offset = 0)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes daily()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes at(string $time)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes dailyAt(string $time)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes twiceDaily(int $first = 1, int $second = 13)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes twiceDailyAt(int $first = 1, int $second = 13, int $offset = 0)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes weekdays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes weekends()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes mondays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes tuesdays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes wednesdays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes thursdays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes fridays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes saturdays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes sundays()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes weekly()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes weeklyOn(array|mixed $dayOfWeek, string $time = '0:0')
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes monthly()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes monthlyOn(int $dayOfMonth = 1, string $time = '0:0')
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes twiceMonthly(int $first = 1, int $second = 16, string $time = '0:0')
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes lastDayOfMonth(string $time = '0:0')
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes quarterly()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes quarterlyOn(int $dayOfQuarter = 1, string $time = '0:0')
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes yearly()
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes yearlyOn(int $month = 1, int|string $dayOfMonth = 1, string $time = '0:0')
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes days(array|mixed $days)
 * @method static \Kasi\Console\Scheduling\PendingEventAttributes timezone(\DateTimeZone|string $timezone)
 *
 * @see \Kasi\Console\Scheduling\Schedule
 */
class Schedule extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConsoleSchedule::class;
    }
}
