<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BroadcastQueueEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public Queue $queue)
    {
    }

    public function broadcastOn(): array
    {
        $doctorId = $this->queue->practiceSession?->doctor_id;

        return [
            new Channel("practice-session.{$this->queue->practice_session_id}"),
            new Channel('practice-overview'),
            new PrivateChannel('staff-panel'),
            new PrivateChannel("doctor.{$doctorId}"),
            new PrivateChannel("queue.{$this->queue->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'queue_id' => $this->queue->id,
            'practice_session_id' => $this->queue->practice_session_id,
            'kode_antrian' => $this->queue->kode_antrian,
            'nomor_urut' => $this->queue->nomor_urut,
            'status' => $this->queue->status,
        ];
    }
}
