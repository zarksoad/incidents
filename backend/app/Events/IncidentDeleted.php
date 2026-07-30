<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $incidentId;

    public function __construct($incidentId)
    {
        $this->incidentId = $incidentId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('incidents'),
        ];
    }
}
