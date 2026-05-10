<?php

namespace App\Events;

use App\Models\MedicalVisit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicalVisitUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public MedicalVisit $visit)
    {
        $this->visit->loadMissing('queue.practiceSession');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("practice-session.{$this->visit->queue->practice_session_id}"),
            new Channel('practice-overview'),
            new PrivateChannel('staff-panel'),
            new PrivateChannel("doctor.{$this->visit->doctor_id}"),
            new PrivateChannel("queue.{$this->visit->queue_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MedicalVisitUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'queue_id' => $this->visit->queue_id,
            'visit_id' => $this->visit->id,
            'doctor_id' => $this->visit->doctor_id,
            'status' => $this->visit->queue->status,
        ];
    }
}
