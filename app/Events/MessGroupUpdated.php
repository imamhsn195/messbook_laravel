<?php

namespace App\Events;

use App\Models\MessGroup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessGroupUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public MessGroup $messGroup;
    /**
     * Create a new event instance.
     */
    public function __construct(MessGroup $messGroup)
    {
        $this->messGroup = $messGroup;
        // Log when the event is created
        Log::info("MessGroupUpdated event dispatched for MessGroup ID: {$messGroup->id}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
