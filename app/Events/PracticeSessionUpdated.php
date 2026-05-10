<?php

namespace App\Events;

use App\Models\PracticeSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PracticeSessionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public PracticeSession $practiceSession)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("practice-session.{$this->practiceSession->id}"),
            new Channel('practice-overview'),
            new PrivateChannel('staff-panel'),
            new PrivateChannel("doctor.{$this->practiceSession->doctor_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PracticeSessionUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'practice_session_id' => $this->practiceSession->id,
            'status' => $this->practiceSession->status,
            'nomor_terakhir' => $this->practiceSession->nomor_terakhir,
        ];
    }
}
