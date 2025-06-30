<?php

namespace Kasi\Support;

class DefaultProviders
{
    /**
     * The current providers.
     *
     * @var array
     */
    protected $providers;

    /**
     * Create a new default provider collection.
     *
     * @return void
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?: [
            \Kasi\Auth\AuthServiceProvider::class,
            \Kasi\Broadcasting\BroadcastServiceProvider::class,
            \Kasi\Bus\BusServiceProvider::class,
            \Kasi\Cache\CacheServiceProvider::class,
            \Kasi\Foundation\Providers\ConsoleSupportServiceProvider::class,
            \Kasi\Concurrency\ConcurrencyServiceProvider::class,
            \Kasi\Cookie\CookieServiceProvider::class,
            \Kasi\Database\DatabaseServiceProvider::class,
            \Kasi\Encryption\EncryptionServiceProvider::class,
            \Kasi\Filesystem\FilesystemServiceProvider::class,
            \Kasi\Foundation\Providers\FoundationServiceProvider::class,
            \Kasi\Hashing\HashServiceProvider::class,
            \Kasi\Mail\MailServiceProvider::class,
            \Kasi\Notifications\NotificationServiceProvider::class,
            \Kasi\Pagination\PaginationServiceProvider::class,
            \Kasi\Auth\Passwords\PasswordResetServiceProvider::class,
            \Kasi\Pipeline\PipelineServiceProvider::class,
            \Kasi\Queue\QueueServiceProvider::class,
            \Kasi\Redis\RedisServiceProvider::class,
            \Kasi\Session\SessionServiceProvider::class,
            \Kasi\Translation\TranslationServiceProvider::class,
            \Kasi\Validation\ValidationServiceProvider::class,
            \Kasi\View\ViewServiceProvider::class,
        ];
    }

    /**
     * Merge the given providers into the provider collection.
     *
     * @param  array  $providers
     * @return static
     */
    public function merge(array $providers)
    {
        $this->providers = array_merge($this->providers, $providers);

        return new static($this->providers);
    }

    /**
     * Replace the given providers with other providers.
     *
     * @param  array  $replacements
     * @return static
     */
    public function replace(array $replacements)
    {
        $current = new Collection($this->providers);

        foreach ($replacements as $from => $to) {
            $key = $current->search($from);

            $current = is_int($key) ? $current->replace([$key => $to]) : $current;
        }

        return new static($current->values()->toArray());
    }

    /**
     * Disable the given providers.
     *
     * @param  array  $providers
     * @return static
     */
    public function except(array $providers)
    {
        return new static((new Collection($this->providers))
            ->reject(fn ($p) => in_array($p, $providers))
            ->values()
            ->toArray());
    }

    /**
     * Convert the provider collection to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->providers;
    }
}
