<?php

namespace App\Events;

class QueueCompleted extends BroadcastQueueEvent
{
    public function broadcastAs(): string
    {
        return 'QueueCompleted';
    }
}
