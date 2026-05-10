<?php

namespace App\Events;

class QueueSkipped extends BroadcastQueueEvent
{
    public function broadcastAs(): string
    {
        return 'QueueSkipped';
    }
}
