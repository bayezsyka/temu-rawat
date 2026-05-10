<?php

namespace App\Events;

class InitialCheckUpdated extends BroadcastQueueEvent
{
    public function broadcastAs(): string
    {
        return 'InitialCheckUpdated';
    }
}
