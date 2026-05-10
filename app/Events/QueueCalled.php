<?php

namespace App\Events;

class QueueCalled extends BroadcastQueueEvent
{
    public function broadcastAs(): string
    {
        return 'QueueCalled';
    }
}
