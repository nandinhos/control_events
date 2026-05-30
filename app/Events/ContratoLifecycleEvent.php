<?php

namespace App\Events;

use App\Models\Contrato;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContratoLifecycleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Contrato $contrato,
        public string $fromEstado,
        public string $toEstado,
    ) {}

    public function broadcastOn(): array
    {
        return [];
    }
}
