<?php

namespace App\Events;

use App\Models\Prescription;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrescriptionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public Prescription $prescription)
    {
        $this->prescription->loadMissing('visit.queue.practiceSession');
    }

    public function broadcastOn(): array
    {
        $visit = $this->prescription->visit;

        return [
            new Channel("practice-session.{$visit->queue->practice_session_id}"),
            new Channel('practice-overview'),
            new PrivateChannel('staff-panel'),
            new PrivateChannel("doctor.{$visit->doctor_id}"),
            new PrivateChannel("queue.{$visit->queue_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PrescriptionUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'queue_id' => $this->prescription->visit->queue_id,
            'visit_id' => $this->prescription->medical_visit_id,
            'prescription_id' => $this->prescription->id,
        ];
    }
}
