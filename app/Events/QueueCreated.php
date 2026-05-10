<?php

namespace App\Events;

class QueueCreated extends BroadcastQueueEvent
{
    public function broadcastAs(): string
    {
        return 'QueueCreated';
    }
}
