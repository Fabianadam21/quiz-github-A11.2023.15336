<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $jadwalId,
        public readonly ?int $nomorAntrian,
        public readonly ?int $completedQueueNumber = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('jadwal.'.$this->jadwalId)];
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'jadwal_id' => $this->jadwalId,
            'nomor_antrian' => $this->nomorAntrian,
            'completed_queue_number' => $this->completedQueueNumber,
        ];
    }
}
