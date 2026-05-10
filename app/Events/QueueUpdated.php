<?php

namespace App\Events;

class QueueUpdated extends BroadcastQueueEvent
{
    public function broadcastAs(): string
    {
        return 'QueueUpdated';
    }
}
