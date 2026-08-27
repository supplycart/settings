<?php

declare(strict_types=1);

namespace Supplycart\Settings\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Supplycart\Settings\Models\Setting;

final class SettingSaved implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public readonly Setting $setting) {}

    #[\Override]
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("settings.{$this->setting->model_type}.{$this->setting->model_id}");
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return $this->setting->values;
    }
}
