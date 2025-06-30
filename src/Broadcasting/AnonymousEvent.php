<?php

namespace Kasi\Broadcasting;

use Kasi\Contracts\Broadcasting\ShouldBroadcast;
use Kasi\Contracts\Support\Arrayable;
use Kasi\Foundation\Events\Dispatchable;
use Kasi\Support\Arr;
use Kasi\Support\Collection;

class AnonymousEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithBroadcasting, InteractsWithSockets;

    /**
     * The connection the event should be broadcast on.
     */
    protected ?string $connection = null;

    /**
     * The name the event should be broadcast as.
     */
    protected ?string $name = null;

    /**
     * The payload the event should be broadcast with.
     */
    protected array $payload = [];

    /**
     * Should the broadcast include the current user.
     */
    protected bool $includeCurrentUser = true;

    /**
     * Indicates if the event should be broadcast synchronously.
     */
    protected bool $shouldBroadcastNow = false;

    /**
     * Create a new anonymous broadcastable event instance.
     *
     * @return void
     */
    public function __construct(protected Channel|array|string $channels)
    {
        $this->channels = Arr::wrap($channels);
    }

    /**
     * Set the connection the event should be broadcast on.
     */
    public function via(string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the name the event should be broadcast as.
     */
    public function as(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the payload the event should be broadcast with.
     */
    public function with(Arrayable|array $payload): static
    {
        $this->payload = $payload instanceof Arrayable
            ? $payload->toArray()
            : (new Collection($payload))->map(
                fn ($p) => $p instanceof Arrayable ? $p->toArray() : $p
            )->all();

        return $this;
    }

    /**
     * Broadcast the event to everyone except the current user.
     */
    public function toOthers(): static
    {
        $this->includeCurrentUser = false;

        return $this;
    }

    /**
     * Broadcast the event.
     */
    public function sendNow(): void
    {
        $this->shouldBroadcastNow = true;

        $this->send();
    }

    /**
     * Broadcast the event.
     */
    public function send(): void
    {
        $broadcast = broadcast($this)->via($this->connection);

        if (! $this->includeCurrentUser) {
            $broadcast->toOthers();
        }
    }

    /**
     * Get the name the event should broadcast as.
     */
    public function broadcastAs(): string
    {
        return $this->name ?: class_basename($this);
    }

    /**
     * Get the payload the event should broadcast with.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Kasi\Broadcasting\Channel|\Kasi\Broadcasting\Channel[]|string[]|string
     */
    public function broadcastOn(): Channel|array
    {
        return $this->channels;
    }

    /**
     * Determine if the event should be broadcast synchronously.
     */
    public function shouldBroadcastNow(): bool
    {
        return $this->shouldBroadcastNow;
    }
}
